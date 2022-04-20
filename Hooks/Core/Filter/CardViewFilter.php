<?php

namespace Modules\AEGIS\Hooks\Core\Filter;

class CardViewFilter
{
    public static function run(&$query, $module, $request)
    {
        if (isset($request->module)
            && $request->module === 'Documents'
            && $request->model === 'Document'
        ) {
            $query
                ->select([
                    'm_documents_documents.*',
                    'm_aegis_variant_documents.reference',
                ])
                ->join('m_aegis_variant_documents', 'm_aegis_variant_documents.document_id', 'm_documents_documents.id')
                ->groupBy('m_documents_documents.id');
        }
    }
}
