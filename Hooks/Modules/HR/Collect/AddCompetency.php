<?php

namespace Modules\AEGIS\Hooks\Modules\HR\Collect;

use Modules\AEGIS\Models\CompetencyDetail;
use Modules\HR\Models\CompetencySection;
use Modules\HR\Models\CompetencySubjectAchievement;

class AddCompetency
{
    public static function run($args)
    {
        if (isset($args['request']->aegis)) {
            $competency_company                = new CompetencyDetail();
            $competency_company->competency_id = $args['competency']->id;
            $competency_company->company_id    = $args['request']->aegis['company'];
            $competency_company->live_document = $args['request']->aegis['live-document'];
            $competency_company->save();
        }
        $default_sections = $args['competency']->user->getMeta('aegis.default-sections');
        if ($default_sections) {
            $competency_sections = CompetencySection
                ::whereIn('id', $default_sections)
                ->active()
                ->with([
                    'groups',
                    'groups.subjects',
                ])
                ->get();
            foreach ($competency_sections as $competency_section) {
                $groups = $competency_section->groups;
                if ($groups) {
                    foreach ($groups as $group) {
                        foreach ($group->subjects as $subject) {
                            CompetencySubjectAchievement::create([
                                'competency_id' => $args['competency']->id,
                                'subject_id'    => $subject->id,
                                'status'        => false,
                                'has_knowledge' => false,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
