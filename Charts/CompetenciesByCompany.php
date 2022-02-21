<?php

namespace Modules\AEGIS\Charts;

use Modules\AEGIS\Models\CompetencyCompany;

class CompetenciesByCompany
{
    public static function run()
    {
        $return = array(
            'data' => [
                'labels' => [],
                'datasets' => array(
                    array(
                        'data'  => array(),
                        'label' => ___('Competencies by Company'),
                    ),
                ),
            ],
            'type' => 'donut',
        );
        $companies = CompetencyCompany
            ::select([
                \DB::raw('count(m_aegis_companies.status) as count,m_aegis_companies.name')
            ])
            ->join('m_aegis_companies', 'm_aegis_competency_company.company_id', '=', 'm_aegis_companies.id')
            ->groupBy('name')
            ->orderBy('status')
            ->get();
        if ($companies) {
            foreach ($companies as $company) {
                $return['data']['labels'][]              = $company->name;
                $return['data']['datasets'][0]['data'][] = $company->count;
            }
        }
        return $return;
    }
}
