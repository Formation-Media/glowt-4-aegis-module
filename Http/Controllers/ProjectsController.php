<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Helpers\Modules;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Helpers\Icons;
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
        $page_menu                = [];
        $phases                   = [];
        $project                  = Project::with('company')->findOrFail($id);
        $types                    = $project->company->types()->where('status', true)->getOrdered()->selectTree();
        $variants                 = $project->variants;

        $customer = $project->customer ?? null;
        $tabs     = [
            'details' => ['name' => 'dictionary.details'],
        ];

        if ($documents_module_enabled) {
            $page_menu[] = array(
                'class' => 'js-add-document',
                'icon'  => 'file-plus',
                'title' => ['phrases.add', ['item' => 'dictionary.document']],
            );
        }

        foreach ($variants as $i => $variant) {
            if ($variant->is_default == true) {
                $default_variant      = $variant;
                $phases[$variant->id] = $variant->title;
                $tabs['default']      = [
                    'name' => ___('aegis::dictionary.phase').' '.$variant->title,
                ];
            } else {
                $phases[$variant->id] = $variant->title;
                $tabs['phase-'.$i]    = ['name' => ___('aegis::dictionary.phase').' '.$variant->title];
            }
        }
        $page_menu[] = array(
            'href'  => '/a/m/AEGIS/projects/add-phase/'.$project->id,
            'icon'  => Icons::phase(),
            'title' => ['phrases.add', ['item' => 'aegis::dictionary.phase']],
        );

        return parent::view(compact(
            'default_variant',
            'documents_module_enabled',
            'project',
            'customer',
            'page_menu',
            'phases',
            'tabs',
            'types',
            'variants'
        ));
    }

    public function add(Request $request, $id = null)
    {
        $companies = Company::MDSS()->ordered()->pluck('name', 'id')->toArray();
        $customer  = Customer::find($id);
        return parent::view(compact(
            'companies',
            'customer',
        ));
    }

    public function add_phase(Request $request, $id)
    {
        $project = Project::find($id);
        return parent::view(compact('project'));
    }
}
