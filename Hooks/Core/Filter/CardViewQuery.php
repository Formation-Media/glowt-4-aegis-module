<?php

namespace Modules\AEGIS\Hooks\Core\Filter;

class CardViewQuery
{
    public static function run(&$query, $module, $request)
    {
        if (isset($request->module)
            && $request->module === 'Documents'
            && $request->model === 'Document'
        ) {
            session([
                'document-card-view' => [
                    'bindings' => $query->getBindings(),
                    'sql'      => $query->toSql(),
                ],
            ]);
            session()->save();
        }
    }
}
