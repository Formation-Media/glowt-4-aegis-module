<?php

namespace Modules\AEGIS\Http\Controllers\Store;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\ProjectVariant;
use Modules\AEGIS\Models\Customer;

class ProjectsController extends Controller
{
    public function add(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:Modules\AEGIS\Models\Company,id',
            'reference'  => 'required|max:4|unique:Modules\AEGIS\Models\Project',
            'customer'   => 'required|exists:Modules\AEGIS\Models\Customer,id',
        ]);
        $company_abbreviation     = Company::find($validated['company_id'])->abbreviation;
        $customer                 = Customer::find($request->customer);
        $user                     = \Auth::user();
        $new_project              = new Project();
        $new_project->company_id  = $validated['company_id'];
        $new_project->customer_id = $request->customer;
        $new_project->name        = $request->name;
        $new_project->type_id     = $request->type;
        $new_project->added_by    = $user->id;
        $new_project->description = $request->description;
        $new_project->reference   = strtoupper($company_abbreviation.'/'.$validated['reference']);
        $new_project->save();
        $default_variant                 = new ProjectVariant();
        $default_variant->name           = $request->name;
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
        $project              = Project::find($id);
        $redirect             = url('a/m/AEGIS/projects/project/'.$id);
        $project->name        = $request->name;
        $project->customer_id = $request->customer;
        $project->type_id     = $request->type;
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
