<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Helpers\Modules;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\Scope;
use Modules\AEGIS\Models\Type;

class ProjectsController extends Controller
{
    public function index()
    {
        return parent::view();
    }

    public function project(Request $request, $id)
    {
        $documents_module_enabled = Modules::isEnabled('Documents');
        $project                  = Project::find($id);
        $scope                    = $project->scope ?? null;
        $tabs                     = [
            ['name' => __('dictionary.details')],
        ];
        $types    = Type::where('status', true)->pluck('name', 'id')->toArray();
        $variants = $project->variants;
        foreach ($variants as $i => $variant) {
            if ($variant->is_default == true) {
                $default_variant = $variant;
                $tabs[]          = ['name' => __('dictionary.default').' ('.$variant->name.')'];
            } else {
                $tabs[] = ['name' => __('dictionary.variant').' '.($i).' ('.$variant->name.')'];
            }
        }

        return parent::view(compact(
            'default_variant',
            'documents_module_enabled',
            'project',
            'scope',
            'tabs',
            'types',
            'variants'
        ));
    }

    public function add(Request $request, $id = null)
    {
        $scope = Scope::find($id);
        $types = Type::where('status', true)->pluck('name', 'id')->toArray();
        return parent::view(compact('scope', 'types'));
    }

    public function add_variant(Request $request, $id)
    {
        $project = Project::find($id);
        return parent::view(compact('project'));
    }
}
