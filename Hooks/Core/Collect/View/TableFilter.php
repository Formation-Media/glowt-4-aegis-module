<?php

namespace Modules\AEGIS\Hooks\Core\Collect\View;

use Modules\AEGIS\Models\Company;

class TableFilter
{
    public static function run($args)
    {
        $compact = [
            'args'
        ];
        if ($args['module'] == 'HR' && $args['method'] == 'table_competencies') {
            $companies = array();
            $company   = isset($args['request']['filter']['company']) ? $args['request']['filter']['company'] : false;

            if ($competency_companies = Company::all()) {
                foreach ($competency_companies as $company) {
                    $companies[$company->id] = $company->name;
                }
            }

            $compact = array_merge(
                $compact,
                [
                    'companies',
                    'company',
                ]
            );
        }
        return view(
            'aegis::_hooks.table-filter',
            compact(...$compact)
        )->render();
    }
}
