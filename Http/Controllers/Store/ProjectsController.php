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

class ProjectsController extends Controller
{
    public function add(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array(
                'company_id'  => 'required|exists:Modules\AEGIS\Models\Company,id',
                'customer'    => 'required|exists:Modules\AEGIS\Models\Customer,id',
                'description' => 'required',
                'name'        => 'required',
                'reference'   => [
                    'max:'.str_pad('', config('settings.aegis.project.character-limit'), 9),
                    'numeric',
                    'required',
                    'unique:Modules\AEGIS\Models\Project',
                ],
                'type' => 'required',
            )
        );
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }
        $validated                = $validator->validated();
        $company_abbreviation     = Company::find($validated['company_id'])->abbreviation;
        $customer                 = Customer::find($validated['customer']);
        $user                     = \Auth::user();
        $new_project              = new Project();
        $new_project->company_id  = $validated['company_id'];
        $new_project->scope_id    = $validated['customer'];
        $new_project->name        = $validated['name'];
        $new_project->type_id     = $validated['type'];
        $new_project->added_by    = $user->id;
        $new_project->description = $validated['description'];
        $new_project->reference   = strtoupper($company_abbreviation.'/'.$validated['reference']);
        $new_project->save();
        $default_variant                 = new ProjectVariant();
        $default_variant->name           = $validated['name'];
        $default_variant->added_by       = $user->id;
        $default_variant->is_default     = true;
        $default_variant->project_id     = $new_project->id;
        $default_variant->reference      = strtoupper($customer->reference.'/'.$validated['reference']);
        $default_variant->variant_number = 0;
        $default_variant->save();
        $redirect = url('a/m/AEGIS/projects/project/'.$new_project->id);
        return redirect($redirect);
    }

    public function add_variant(Request $request, $id)
    {
        $request->validate([
            'variant_number' => [
                'required',
                'max:2',
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
        $new_variant->reference      = strtoupper($project->reference.'/'. $request->variant_number.'/');
        $new_variant->variant_number = $request->variant_number;
        $new_variant->save();
        return redirect($redirect);
    }

    public function project(Request $request, $id)
    {
        $project           = Project::find($id);
        $redirect          = url('a/m/AEGIS/projects/project/'.$id);
        $project->name     = $request->name;
        $project->scope_id = $request->customer;
        $project->type_id  = $request->type;
        $project->update();
        $default_project_variant       = ProjectVariant::where('project_id', $project->id)->where('is_default', true)->first();
        $default_project_variant->name = $request->name;
        $default_project_variant->update();

        return redirect($redirect);
    }

    public function project_variant(Request $request, $id)
    {
        $project_variant       = ProjectVariant::find($id);
        $redirect              = url('a/m/AEGIS/projects/project/'.$project_variant->project_id);
        $project_variant->name = $request->name;
        $project_variant->save();
        return redirect($redirect);
    }
}
