<?php

namespace Modules\AEGIS\Http\Controllers\Store;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\ProjectVariant;

class ProjectsController extends Controller
{
    public function add(Request $request){
        $new_project = new Project();
        $new_project->scope_id = $request->scope;
        $new_project->name = $request->name;
        $new_project->type = $request->type;
        $new_project->added_by = \Auth::id();
        $new_project->save();
        $default_variant = new ProjectVariant();
        $default_variant->name = $request->name;
        $default_variant->is_default = true;
        $default_variant->project_id = $new_project->id;
        $default_variant->added_by = \Auth::id();
        $default_variant->save();
        $redirect = url('a/m/Aegis/projects/project/'.$new_project->id);

        return redirect($redirect);
    }

    public function add_variant(Request $request , $id){
        $redirect = url('a/m/Aegis/projects/project/'.$id);
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
        $redirect = url('a/m/Aegis/projects/project/'.$id);
        $project->name = $request->name;
        $project->scope_id = $request->scope;
        $project->update();

        return redirect($redirect);
    }

    public function project_variant(Request $request, $id){
        $project_variant = ProjectVariant::find($id);
        $redirect = url('a/m/Aegis/projects/project/'.$project_variant->project_id);
        $project_variant->name = $request->name;

        return redirect($redirect);

    }
}
