<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\AEGIS\Models\CompetencyCompany;

class ChartsController extends Controller
{
    public static function competencies_by_company()
    {
        $dataset = array(
            'labels' => array(),
            'data'   => array(),
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
                $dataset['labels'][] = $company->name;
                $dataset['data'][]   = $company->count;
            }
        }
        return array(
            'data'  => $dataset,
            'title' => ___('Competencies by Company'),
            'type'  => 'donut',
        );
    }
}
