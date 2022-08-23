<?php

namespace Modules\AEGIS\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Notifications\Toast;
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
        if ($projects = Project
            ::with('phases')
            ->search(
                array(
                    'name',
                    'reference',
                ),
                '%'.$request->term.'%'
            )
            ->paged()
        ) {
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
    public function check_issue($request)
    {
        $category        = Category::find($request->category);
        $project_variant = ProjectVariant::find($request->project_variant);
        $reference       = $project_variant->project->reference.'/'.$category->prefix
                            .str_pad($request->reference, 2, '0', STR_PAD_LEFT);
        $return          = [
            'issue'             => 0,
            'previous_document' => null,
        ];

        $issues = VariantDocument::where('reference', $reference);

        if ($issues->count()) {
            $last_issue = $issues->with('document')->orderBy('created_at', 'desc')->first();
            if ($last_issue->document->status !== 'Approved') {
                \Auth::user()->notify(new Toast(
                    'aegis::messages.previous-issue-not-approved.title',
                    'aegis::messages.previous-issue-not-approved.message',
                ));
                return false;
            }
        }

        $return['issue'] = $issues->count() + 1;

        if ($return['issue'] > 1) {
            $return['previous_document'] = $issues
                ->with([
                    'document',
                    'document.metas',
                ])
                ->orderBy('issue', 'desc')
                ->first();
        }
        return $return;
    }
    public function get_project_variants($request)
    {
        $variants = [];
        if (isset($request->project)) {
            $project         = Project::find($request->project);
            $reference       = $project->reference;
            $default_variant = $project->variants->where('is_default', true)->first();
            if ($default_variant) {
                $default_variant = $default_variant->id;
            }
            foreach ($project->variants as $variant) {
                $variants[$variant->id] = $variant->variant_number.' - '.$variant->name;
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
            ::where('reference', 'REGEXP', '^'.$reference_prefix.'[0-9]+')
            ->orderBy('created_at', 'desc')
            ->first();
        if ($last_variant) {
            $next_reference = (int) str_replace($reference_prefix, '', $last_variant->reference) + 1;
        } else {
            $next_reference = 1;
        }
        return array(
            'prefix'    => $reference_prefix,
            'reference' => str_pad($next_reference, 2, '0', STR_PAD_LEFT),
        );
    }
    public function table_variantdocumentsview($request)
    {
        $actions = array(
            array(
                'style' => 'primary',
                'name'  => ___('View'),
                'href'  => '/a/m/Documents/document/document/{{document_id}}',
            ),
        );
        $row_structure = array(
            'actions' => $actions,
            'data'   => array(
                'ID' => array(
                    'columns' => 'id',
                    'display' => false,
                ),
                'DOCUMENT ID' => array(
                    'columns' => 'document_id',
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
