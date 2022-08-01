<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Helpers\Modules;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\Customer;
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
        $customer                 = $project->customer ?? null;
        $tabs                     = [
            'details' => ['name' => 'dictionary.details'],
        ];
        $types    = Type::where('status', true)->ordered()->pluck('name', 'id')->toArray();
        $variants = $project->variants;
        foreach ($variants as $i => $variant) {
            if ($variant->is_default == true) {
                $default_variant = $variant;
                $tabs['default'] = ['name' => ___('dictionary.default').' ('.$variant->name.')'];
            } else {
                $tabs['phase-'.$i] = ['name' => ___('aegis::dictionary.phase').' '.($i).' ('.$variant->name.')'];
            }
        }
        $page_menu = array(
            array(
                'href'  => '/a/m/AEGIS/projects/add-phase/'.$project->id,
                'icon'  => 'file-plus',
                'title' => ['phrases.add', ['item' => 'aegis::dictionary.phase']],
            ),
        );

        return parent::view(compact(
            'default_variant',
            'documents_module_enabled',
            'project',
            'customer',
            'page_menu',
            'tabs',
            'types',
            'variants'
        ));
    }

    public function add(Request $request, $id = null)
    {
        $companies = Company::MDSS()->ordered()->pluck('name', 'id')->toArray();
        $customer  = Customer::find($id);
        $types     = Type::where('status', true)->ordered()->pluck('name', 'id')->toArray();
        return parent::view(compact(
            'companies',
            'customer',
            'types'
        ));
    }

    public function add_phase(Request $request, $id)
    {
        $project = Project::find($id);
        return parent::view(compact('project'));
    }
}
