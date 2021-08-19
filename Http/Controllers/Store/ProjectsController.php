<?php

namespace Modules\AEGIS\Http\Controllers\Store;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\ProjectVariant;

class ProjectsController extends Controller
{
    public function add(Request $request){
        $user = \Auth::user();
        $new_project = new Project();
        $new_project->scope_id = $request->scope;
        $new_project->name = $request->name;
        $new_project->type_id = $request->type;
        $new_project->added_by = $user->id;
        $new_project->save();
        $default_variant = new ProjectVariant();
        $default_variant->name = $request->name;
        $default_variant->is_default = true;
        $default_variant->project_id = $new_project->id;
        $default_variant->added_by = $user->id;
        $default_variant->save();
        $redirect = url('a/m/AEGIS/projects/project/'.$new_project->id);

        return redirect($redirect);
    }

    public function add_variant(Request $request , $id){
        $redirect = url('a/m/AEGIS/projects/project/'.$id);
        $new_variant = new ProjectVariant();
        $new_variant->name = $request->name;
        $new_variant->is_default = false;
        $new_variant->project_id = $id;
        $new_variant->added_by = \Auth::id();
        $new_variant->save();

        return redirect($redirect);
    }

    public function project(Request $request, $id){
        $project = Project::find($id);
        $redirect = url('a/m/AEGIS/projects/project/'.$id);
        $project->name = $request->name;
        $project->scope_id = $request->scope;
        $project->update();

        $default_project_variant = ProjectVariant::where('project_id', $project->id)->where('is_default', true)->first();
        $default_project_variant->name = $request->name;
        $default_project_variant->update();

        return redirect($redirect);
    }

    public function project_variant(Request $request, $id){
        $project_variant = ProjectVariant::find($id);
        $redirect = url('a/m/AEGIS/projects/project/'.$project_variant->project_id);
        $project_variant->name = $request->name;
        $project_variant->save();

        return redirect($redirect);

    }
}
