<?php

namespace Modules\AEGIS\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\Scope;

class ProjectsController extends Controller
{
    public function index(){
        return parent::view();
    }

    public function project(Request $request, $id){
        $project = Project::find($id);
        $scope = $project->scope?? null;
        return parent::view(compact('project', 'scope'));
    }

    public function add(Request $request, $id=null){
        $scope = Scope::find($id);
        return parent::view(compact('scope'));
    }

    public function add_variant(Request $request, $id){
        $project = Project::find($id);
        return parent::view(compact('project'));
    }
}
