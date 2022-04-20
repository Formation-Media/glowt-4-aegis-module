<?php

namespace Modules\AEGIS\Hooks\Core\Collect;

class DashboardCharts
{
    public static function run()
    {
        $dashboard_charts = array();
        if (\Auth::user()->has_role('HR::HR Manager')) {
            $dashboard_charts['Competencies by Company'] = array(
                'method' => 'chart_competencies_by_company',
            );
        }
        return $dashboard_charts;
    }
}
