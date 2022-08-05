<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Collect\View;

use Modules\AEGIS\Helpers\Icons;

class TopOfDocumentsPage
{
    public static function run($args)
    {
        $links = [
            [
                'href'  => '/a/m/AEGIS/projects',
                'icon'  => Icons::projects(),
                'title' => 'dictionary.projects',
            ],
            [
                'href'  => '/a/m/Documents/templates',
                'icon'  => Icons::templates(),
                'title' => 'dictionary.templates',
            ],
            [
                'href'  => '/a/m/AEGIS/training',
                'icon'  => Icons::training(),
                'title' => 'aegis::dictionary.training',
            ],
        ];
        return view(
            'aegis::_hooks.documents.quick-links',
            compact(
                'links',
            )
        );
    }
}
