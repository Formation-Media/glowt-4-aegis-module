<?php

namespace Modules\AEGIS\Hooks\Core\Filter;

use Illuminate\Database\Eloquent\Builder;
use Modules\AEGIS\Models\DocumentApprovalItemDetails;
use Modules\AEGIS\Models\VariantDocument;

class CardViewFilter
{
    public static function run(&$query, $module, $request)
    {
        $filter     = $request->filter;
        $phase_id   = null;
        $project_id = null;
        if (isset($request->module)
            && $request->module === 'Documents'
            && $request->model === 'Document'
        ) {
            if (isset($filter['project']) && !is_null($filter['project'])) {
                $project_id = $filter['project'];
                if (array_key_exists('phase', $filter) && !is_null($filter['phase'])) {
                    $phase_id = $filter['phase'];
                }
            } elseif ($request->_GET['module'] === 'AEGIS'
                && $request->_GET['feature'] === 'projects'
                && $request->_GET['page'] === 'project'
            ) {
                $project_id = $request->_GET['id'];
                $phase_id   = $request->dataset['phase'];
            }

            $query
                ->select([
                    'm_documents_documents.*',
                    'm_aegis_variant_documents.reference',
                ])
                ->join('m_aegis_variant_documents', 'm_aegis_variant_documents.document_id', 'm_documents_documents.id')
                ->groupBy('m_documents_documents.id');
            if (!is_null($project_id)) {
                $variant_documents = VariantDocument
                    ::whereHas('project', function (Builder $query) use ($project_id) {
                        $query->where('m_aegis_projects.id', $project_id);
                    });
                $query->whereIn('m_documents_documents.id', $variant_documents->pluck('document_id'));
                if (!is_null($phase_id)) {
                    $query->where('m_aegis_variant_documents.variant_id', $phase_id);
                }
            }
            if (isset($filter['role']) && !is_null($filter['role'])) {
                $item_details = DocumentApprovalItemDetails
                    ::where('job_title_id', $filter['role'])
                    ->whereHas('item', function (Builder $query) use ($request) {
                        $query->where('agent_id', $request->_GET['id']);
                    })
                    ->with('item')
                    ->get()
                    ->toArray();
                $query->whereIn('m_documents_documents.id', array_column(array_column($item_details, 'item'), 'document_id'));
            }
        }
    }
}
