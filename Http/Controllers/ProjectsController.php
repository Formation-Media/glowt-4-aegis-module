<?php

namespace Modules\AEGIS\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\AEGIS\Models\Project;

class ProjectsController extends Controller
{
    public function index()
    {
        return parent::view();
    }

    public function project(Request $request, $id)
    {
        $project = Project::find($id);
        return parent::view(compact('project'));
    }

    public function add(){
        return parent::view();
    }

    public function add_variant(Request $request, $id){
        $project = Project::find($id);
        return parent::view(compact('project'));
    }
}
