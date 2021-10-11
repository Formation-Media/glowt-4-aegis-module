<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\Scope;
use Modules\AEGIS\Models\Type;
use Nwidart\Modules\Facades\Module;

class ProjectsController extends Controller
{
    public function index(){
        return parent::view();
    }

    public function project(Request $request, $id){
        $modules = Module::allEnabled();
        $documents_module_enabled = array_key_exists('Documents', $modules);
        $project = Project::find($id);
        $scope = $project->scope?? null;
        $tabs = [
            [
                'name' => __('Details')
            ],
        ];
        $types = Type::where('status', true)->pluck('name', 'id')->toArray();
        $variants = $project->variants;
        foreach($variants as $i=>$variant){
            if($variant->is_default == true){
                $tabs[]= ['name'=>__('dictionary.default').' ('.$variant->name.')'];
            } else {
                $tabs[] = ['name' => __('aegis::projects.variant').' '.($variant->variant_number).' ('.$variant->name.')'];
            }
        }
        $default_variant = $project->variants->where('is_default', true)->first();
        return parent::view(compact(
            'documents_module_enabled',
            'default_variant',
            'project',
            'scope',
            'tabs',
            'types',
            'variants'
        ));
    }

    public function add(Request $request, $id=null){
        $scope = Scope::find($id);
        $types = Type::where('status', true)->pluck('name', 'id')->toArray();
        return parent::view(compact('scope', 'types'));
    }

    public function add_variant(Request $request, $id){
        $project = Project::find($id);
        return parent::view(compact('project'));
    }
}
