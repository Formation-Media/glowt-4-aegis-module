<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Collect\View;

class TopOfDocumentsPage
{
    public static function run($args)
    {
        $links = [
            [
                'href'  => '/a/m/AEGIS/projects',
                'icon'  => 'folder',
                'title' => 'dictionary.projects',
            ],
            [
                'href'  => '/a/m/Documents/templates',
                'icon'  => 'memo-circle-check',
                'title' => 'dictionary.templates',
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
