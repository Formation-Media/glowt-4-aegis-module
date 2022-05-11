<?php

namespace Modules\AEGIS\Hooks\Modules\HR\Collect\View;

use Modules\AEGIS\Models\Company;

class AddCompetencyFields
{
    public static function run()
    {
        $company_data = Company::all();
        $companies    = array();
        $details      = [];
        if (count($company_data)) {
            foreach ($company_data as $company) {
                $companies[$company->id] = $company->name;
            }
        }
        $live_document = null;
        return view(
            'aegis::_hooks.add-competency-fields',
            compact(
                'companies',
                'details',
                'live_document'
            )
        );
    }
}
