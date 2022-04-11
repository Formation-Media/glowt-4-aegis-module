<?php

namespace Modules\AEGIS\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\ProjectVariant;
use Modules\AEGIS\Models\Customer;
use Modules\AEGIS\Models\VariantDocument;
use Modules\Documents\Models\Category;

class ProjectsController extends Controller
{
    public function autocomplete_projects(Request $request)
    {
        $return = array();
        if ($projects = Project::search(
            array(
                'name',
                'reference',
            ),
            '%'.$request->term.'%'
        )->paged()) {
            foreach ($projects as $project) {
                $return[] = array(
                    'data'    => $project,
                    'value'   => $project->id,
                    'content' => $project->title,
                );
            }
        }
        return $return;
    }

    public function delete_project($request)
    {
        foreach ($request->ids as $id) {
            $project          = Project::find($id);
            $project_variants = ProjectVariant::where('project_id', $project->id)->get();
            foreach ($project_variants as $variant) {
                $variant->delete();
            }
            $project->delete();
        }
    }
    public function get_issue($request)
    {
        $category         = Category::find($request->category);
        $project_variant  = ProjectVariant::find($request->project_variant);
        $reference        = $project_variant->project->reference.'/'.$category->prefix
                                .str_pad($request->reference, 2, '0', STR_PAD_LEFT);
        $issue            = VariantDocument
            ::where('reference', $reference)
            ->count();
        return $issue + 1;
    }
    public function get_project_variants($request)
    {
        $variants = [];
        if (isset($request->project)) {
            $project         = Project::find($request->project);
            $reference       = $project->reference;
            $variants        = $project->variants->pluck('name', 'id')->toArray();
            $default_variant = $project->variants->where('is_default', true)->first();
            if ($default_variant) {
                $default_variant = $default_variant->id;
            }
        }
        return compact(
            'default_variant',
            'reference',
            'variants'
        );
    }
    public function get_customer_ref($request)
    {
        $customer = Customer::find($request->id);
        return array(
            'prefix' => $customer->reference.'/',
        );
    }
    public function get_variant_ref($request)
    {
        $category         = Category::find($request->category);
        $project_variant  = ProjectVariant::find($request->project_variant);
        $reference_prefix = $project_variant->reference.'/'.$category->prefix;
        $last_variant     = VariantDocument
            ::where('reference', 'like', $reference_prefix.'%')
            ->orderBy('created_at', 'desc')
            ->first();
        if ($last_variant) {
            $next_reference = str_replace($reference_prefix, '', $last_variant->reference) + 1;
        } else {
            $next_reference = 1;
        }
        return array(
            'prefix'    => $reference_prefix,
            'reference' => str_pad($next_reference, 2, '0', STR_PAD_LEFT),
        );
    }

    public function table_view($request)
    {
        $actions = array(
            array(
                'style' => 'primary',
                'name'  => ___('View'),
                'uri'   => '/a/m/AEGIS/projects/project/{{id}}',
            ),
        );
        $global_actions = array(
            array(
                'action' => 'delete-project',
                'style'  => 'danger',
                'title'  => ___('Delete'),
            ),
        );
        $row_structure = array(
            'actions' => $actions,
            'data'    => array(
                'ID' => array(
                    'columns' => 'id',
                    'display' => false,
                ),
                'dictionary.reference' => array(
                    'columns'      => 'reference',
                    'default_sort' => 'desc',
                    'sortable'     => true,
                ),
                'dictionary.title' => array(
                    'columns'  => 'name',
                    'sortable' => true,
                ),
                'dictionary.company' => array(
                    'columns'  => 'm_aegis_companies.name',
                    'sortable' => true,
                ),
                'dictionary.type' => array(
                    'sortable' => true,
                ),
                'phrases.added-by' => array(
                    'sortable' => true,
                ),
                'phrases.added-at' => array(
                    'columns'      => 'created_at',
                    'sortable'     => true,
                    'class'        => '\App\Helpers\Dates',
                    'method'       => 'datetime',
                ),
                'phrases.updated-on' => array(
                    'columns'  => 'updated_at',
                    'sortable' => true,
                    'class'    => '\App\Helpers\Dates',
                    'method'   => 'datetime',
                ),
            ),
        );
        return parent::to_ajax_table(
            Project::class,
            $row_structure,
            $global_actions,
            function ($query) use ($request) {
                $query->join('m_aegis_companies', 'm_aegis_companies.id', 'm_aegis_projects.company_id');
                if ($request->id) {
                    return $query->where('scope_id', $request->id);
                }
                return $query;
            },
            function ($in, $out) {
                $project                 = Project::where('id', $in['id'])->first();
                $added_by                = User::where('id', $project->added_by)->first();
                $out['phrases.added-by'] = $added_by->name;
                $out['dictionary.type']  = $project->type->name;
                return $out;
            }
        );
    }
    public function table_variantdocumentsview($request)
    {
        $actions = array(
            array(
                'style' => 'primary',
                'name'  => ___('View'),
                'uri'   => '/a/m/Documents/document/document/{{document_id}}',
            ),
        );
        $row_structure = array(
            'actions' => $actions,
            'data'   => array(
                'ID' => array(
                    'columns' => 'id',
                    'display' => false,
                ),
                'created_by' => array(
                    'columns' => 'm_documents_documents.created_by',
                    'display' => false,
                ),
                'dictionary.reference' => array(
                    'columns'      => 'reference',
                    'default_sort' => 'desc',
                    'sortable'     => true,
                ),
                'dictionary.title' => array(
                    'columns'      => 'm_documents_documents.name',
                    'default_sort' => 'asc',
                    'sortable'     => true,
                ),
                'dictionary.status' => array(
                    'columns'  => 'm_documents_documents.status',
                    'sortable' => true,
                ),
                'phrases.added-by' => array(),
                'phrases.added-at' => array(
                    'columns'      => 'm_documents_documents.created_at',
                    'sortable'     => true,
                    'class'        => '\App\Helpers\Dates',
                    'method'       => 'datetime',
                ),
                'phrases.updated-on' => array(
                    'columns'  => 'updated_at',
                    'sortable' => true,
                    'class'    => '\App\Helpers\Dates',
                    'method'   => 'datetime',
                ),
            ),
        );
        return parent::to_ajax_table(
            VariantDocument::class,
            $row_structure,
            [],
            function ($query) use ($request) {
                return $query
                    ->where('variant_id', $request->id)
                    ->join('m_documents_documents', 'm_documents_documents.id', 'm_aegis_variant_documents.document_id');
            },
            function ($db, $processed) {
                $processed['phrases.added-by'] = $db['created_by']['first_name'].' '.$db['created_by']['last_name'];
                return $processed;
            }
        );
    }
}
