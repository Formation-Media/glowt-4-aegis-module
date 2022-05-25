<?php

namespace Modules\AEGIS\Hooks\Core\Filter;

use App\Helpers\Modules;

class Breadcrumbs
{
    public static function run(&$breadcrumbs)
    {
        if (Modules::isEnabled('Documents')) {
            $temp_breadcrumbs = [];
            foreach ($breadcrumbs as $link => $breadcrumb) {
                if ((count($breadcrumbs) === 1 && $breadcrumb === 'Documents') || $link === 'm/Documents/document') {
                    $breadcrumb = 'MDSS';
                }
                $temp_breadcrumbs[$link] = $breadcrumb;
            }
            $breadcrumbs = $temp_breadcrumbs;
        }
    }
}
