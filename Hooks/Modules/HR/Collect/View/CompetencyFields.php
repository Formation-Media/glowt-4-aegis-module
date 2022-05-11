<?php

namespace Modules\AEGIS\Hooks\Modules\HR\Collect\View;

use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\CompetencyDetail;

class CompetencyFields
{
    public static function run($competency)
    {
        $company_data       = Company::all();
        $companies          = array();
        $competency_details = CompetencyDetail::where('competency_id', $competency->id)->first();
        $details            = [];
        $live_document      = $competency_details->live_document ?? $competency->user->getMeta('aegis.live-document');
        if ($live_document) {
            $details['aegis::phrases.live-document'] = '<a href="'.$live_document.'" target="_blank">'.___('dictionary.view').'</a>';
        }
        if (count($company_data)) {
            foreach ($company_data as $company) {
                $companies[$company->id] = $company->name;
            }
        }
        if ($bio = $competency->user->getMeta('hr.bio') ?? null) {
            $bio = nl2br($bio);
        }
        return view(
            'aegis::_hooks.add-competency-fields',
            compact(
                'bio',
                'competency',
                'competency_details',
                'companies',
                'details',
                'live_document',
            )
        );
    }
}
