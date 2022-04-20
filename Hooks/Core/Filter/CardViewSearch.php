<?php

namespace Modules\AEGIS\Hooks\Core\Filter;

class CardViewSearch
{
    public static function run(&$search_columns, $module, $request)
    {
        if (isset($request->module)
            && $request->module === 'Documents'
            && $request->model === 'Document'
        ) {
            $search_columns = array_merge(
                [
                    'm_aegis_variant_documents.reference'
                ],
                $search_columns
            );
        }
    }
}
