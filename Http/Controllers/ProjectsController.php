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
        $tabs = [
            [
                'name' => 'Details'
            ],
        ];

        $variants = $project->variants;
        foreach($variants as $i=>$variant){
            if($variant->is_default == true){
                $tabs[]= ['name'=>__('Default ').' ('.$variant->name.')'];
            } else {
                $tabs[] = ['name' => __('Variant ').($i).' ('.$variant->name.')'];
            }

        }

        $default_variant = $project->variants->where('is_default', true)->first();

        return parent::view(compact(
            'default_variant',
            'project',
            'scope',
            'tabs',
            'variants'
        ));
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
