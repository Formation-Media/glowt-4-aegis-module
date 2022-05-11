<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Collect\View;

use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\JobTitle;

class ApproveFields
{
    public static function run()
    {
        $companies  = Company::MDSS()->active()->ordered()->pluck('name', 'id');
        $job_titles = JobTitle::whereIn('id', (array) \Auth::user()->getMeta('aegis.discipline'))->ordered()->formatted();
        return view(
            'aegis::_hooks.approve-fields',
            compact(
                'companies',
                'job_titles',
            )
        );
    }
}
