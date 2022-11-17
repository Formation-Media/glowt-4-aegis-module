<?php

namespace Modules\AEGIS\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\ProjectVariant;
use Modules\AEGIS\Models\Customer;
use Modules\AEGIS\Models\Type;

class ProjectsController extends Controller
{
    public function add(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array(
                'company_id'  => 'required|exists:'.Company::class.',id',
                'customer'    => 'required|exists:'.Customer::class.',id',
                'description' => 'nullable',
                'name'        => 'required',
                'reference'   => [
                    'max:'.str_pad('', config('settings.aegis.project.character-limit'), 9),
                    'numeric',
                    'required',
                ],
                'status' => 'required|boolean',
                'type'   => 'required|exists:'.Type::class.',id',
            )
        );
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
        $validated            = $validator->validated();
        $company_abbreviation = Company::find($validated['company_id'])->abbreviation;
        $project_reference    = strtoupper($company_abbreviation.'/'.$validated['reference']);

        $validator->after(function ($validator) use ($project_reference) {
            if (Project::where('reference', $project_reference)->count()) {
                $validator->errors()->add(
                    'global_errors',
                    'aegis::messages.project.reference-exists',
                );
            }
        });
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
        $user                     = \Auth::user();
        $new_project              = new Project();
        $new_project->company_id  = $validated['company_id'];
        $new_project->scope_id    = $validated['customer'];
        $new_project->name        = $validated['name'];
        $new_project->status      = true;
        $new_project->type_id     = $validated['type'];
        $new_project->added_by    = $user->id;
        $new_project->description = $validated['description'] ?? '';
        $new_project->reference   = $project_reference;
        $new_project->save();
        $default_variant                 = new ProjectVariant();
        $default_variant->name           = 'Default';
        $default_variant->added_by       = $user->id;
        $default_variant->is_default     = true;
        $default_variant->project_id     = $new_project->id;
        $default_variant->reference      = $project_reference;
        $default_variant->variant_number = 0;
        $default_variant->save();
        $redirect = url('a/m/AEGIS/projects/project/'.$new_project->id);
        return redirect($redirect);
    }

    public function add_phase(Request $request, $id)
    {
        $request->validate([
            'variant_number' => [
                'required',
                Rule::unique('m_aegis_project_variants')->where(function ($query) use ($id) {
                    return $query->where('project_id', $id);
                }),
            ],
        ]);
        $project                     = Project::find($id);
        $redirect                    = url('a/m/AEGIS/projects/project/'.$id);
        $new_variant                 = new ProjectVariant();
        $new_variant->added_by       = \Auth::id();
        $new_variant->description    = $request->description;
        $new_variant->name           = $request->name;
        $new_variant->is_default     = false;
        $new_variant->project_id     = $id;
        $new_variant->reference      = strtoupper($project->reference.'/'.str_pad($request->variant_number, 2, "0", STR_PAD_LEFT));
        $new_variant->variant_number = $request->variant_number;
        $new_variant->save();
        return redirect($redirect);
    }
    public function phase(Request $request, $id)
    {
        $project_variant       = ProjectVariant::find($id);
        $redirect              = url('a/m/AEGIS/projects/project/'.$project_variant->project_id);
        $project_variant->name = $request->name;
        $project_variant->save();
        return redirect($redirect);
    }
    public function project(Request $request, $id)
    {
        $project = Project::findOrFail($id);
        $user    = \Auth::user();
        return parent::validate(
            $request,
            [
                'customer'    => 'required|exists:'.Customer::class.',id',
                'description' => 'nullable',
                'name'        => 'required',
                'reference'   => ($user->is_administrator || $user->is_manager) ? [
                    'required',
                    Rule::unique('m_aegis_projects')->ignore($project),
                ] : 'nullable',
                'status' => 'required|boolean',
                'type'   => 'required|exists:m_aegis_types,id',
            ],
            function ($validated) use ($project, $user) {
                $redirect = url('a/m/AEGIS/projects/project/'.$project->id);

                $project->name        = $validated['name'];
                $project->scope_id    = $validated['customer'];
                $project->type_id     = $validated['type'];
                $project->description = $validated['description'] ?? '';
                $project->status      = $validated['status'] ?? '';
                if ($user->is_administrator || $user->is_manager) {
                    $project->reference = $validated['reference'];
                }
                $project->update();

                return redirect($redirect);
            },
        );
    }
}
