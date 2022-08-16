<?php

namespace Modules\AEGIS\Hooks\Core\Collect\View;

class Management
{
    public static function run()
    {
        return array(
            'https://docs.google.com/spreadsheets/d/1eLkArAaq6EjZlpqFrnoEaYgg36LUqO7XzwC7VaT8-bQ/edit#gid=0'
                => '- Formation Query Log',
            '/a/m/AEGIS/companies'                      => 'aegis::phrases.aegis-companies',
            '/a/m/AEGIS/customers'                      => 'dictionary.customers',
            '/a/m/AEGIS/management/feedback-list-types' => 'aegis::phrases.feedback-list-types',
            '/a/m/AEGIS/management/import'              => 'dictionary.import',
            '/a/m/AEGIS/management/job-titles'          => 'aegis::phrases.job-titles',
            '/a/m/AEGIS/management/project-types'       => 'aegis::phrases.project-types',
            '/a/m/AEGIS/management/user-grades'         => 'aegis::phrases.user-grades',
        );
    }
}
