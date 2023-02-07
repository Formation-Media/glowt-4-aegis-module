<?php

namespace Modules\AEGIS\Hooks\Core\Collect\View;

class Management
{
    public static function run()
    {
        return array(
            '/a/m/AEGIS/companies'                      => 'aegis::phrases.aegis-companies',
            '/a/m/AEGIS/customers'                      => 'dictionary.customers',
            '/a/m/AEGIS/management/feedback-list-types' => 'aegis::phrases.feedback-list-types',
            // '/a/m/AEGIS/management/import'              => 'dictionary.import',
            '/a/m/AEGIS/management/job-titles'          => 'aegis::phrases.job-titles',
            '/a/m/AEGIS/management/project-types'       => 'aegis::phrases.project-types',
            '/a/m/AEGIS/management/project-management'  => 'aegis::phrases.project-management',
            '/a/m/AEGIS/management/user-grades'         => 'aegis::phrases.user-grades',
        );
    }
}
