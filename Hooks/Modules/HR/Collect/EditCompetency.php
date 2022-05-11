<?php

namespace Modules\AEGIS\Hooks\Modules\HR\Collect;

use Modules\AEGIS\Models\CompetencyDetail;

class EditCompetency
{
    public static function run($args)
    {
        if (isset($args['request']->aegis)) {
            if ($cc = CompetencyDetail::where('competency_id', $args['competency']->id)->first()) {
                $cc->update([
                    'company_id'    => $args['request']->aegis['company'],
                    'live_document' => $args['request']->aegis['live-document'],
                ]);
            } else {
                $competency_company                = new CompetencyDetail;
                $competency_company->competency_id = $args['competency']->id;
                $competency_company->company_id    = $args['request']->aegis['company'];
                $competency_company->live_document = $args['request']->aegis['live-document'];
                $competency_company->save();
            }
        }
    }
}
