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
        $reference_prefix = $project_variant->project->reference.'/'.$category->prefix;
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
                ___('dictionary.reference') => array(
                    'columns'  => 'reference',
                    'sortable' => true,
                ),
                ___('Name') => array(
                    'columns'  => 'name',
                    'sortable' => true,
                ),
                ___('Type') => array(
                    'sortable' => true,
                ),
                ___('Added By') => array(
                    'sortable' => true,
                ),
                ___('Added at') => array(
                    'columns'      => 'created_at',
                    'default_sort' => 'desc',
                    'sortable'     => true,
                    'class'        => '\App\Helpers\Dates',
                    'method'       => 'datetime',
                ),
                ___('Updated at') => array(
                    'columns'  => 'updated_at',
                    'sortable' => true,
                    'class'    => '\App\Helpers\Dates',
                    'method'   => 'datetime',
                ),
            ),
        );
        return parent::to_ajax_table(
            'Project',
            $row_structure,
            $global_actions,
            function ($query) use ($request) {
                if ($request->id) {
                    return $query->where('scope_id', $request->id);
                }
                return $query;
            },
            function ($in, $out) {
                $project              = Project::where('id', $in['id'])->first();
                $added_by             = User::where('id', $project->added_by)->first();
                $out[___('Added By')] = $added_by->name;
                $out[___('Type')]     = $project->type->name;
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
                'DOCUMENT_ID' => array(
                    'columns' => 'document_id',
                    'display' => false,
                ),
                ___('Name') => array(
                    'default_sort' => 'asc',
                    'sortable'     => true,
                ),
                ___('Status') => array(
                    'sortable' => true,
                ),
                ___('Created By') => array(
                    'sortable' => true,
                ),
                ___('Added at') => array(
                    'columns'  => 'created_at',
                    'sortable' => true,
                    'class'    => '\App\Helpers\Dates',
                    'method'   => 'datetime',
                ),
                ___('Updated at') => array(
                    'columns'  => 'updated_at',
                    'sortable' => true,
                    'class'    => '\App\Helpers\Dates',
                    'method'   => 'datetime',
                ),
            ),
        );
        return parent::to_ajax_table(
            'VariantDocument',
            $row_structure,
            [],
            function ($query) use ($request) {
                return $query->where('variant_id', $request->id);
            },
            function ($in, $out) {
                $variant_document      = VariantDocument::where('id', $in['id'])->first();
                $added_by              = $variant_document->document->created_by;
                $out[___('Name')]       = $variant_document->document->name;
                $out[___('Status')]     = $variant_document->document->status;
                $out[___('Created By')] = $added_by->name;
                return $out;
            }
        );
    }
}
