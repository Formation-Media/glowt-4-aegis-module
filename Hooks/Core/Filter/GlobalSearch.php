<?php

namespace Modules\AEGIS\Hooks\Core\Filter;

use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\VariantDocument;

class GlobalSearch
{
    public static function run(&$searches, $request)
    {
        $searches[Project::class] = [
            'icon'   => 'folder',
            'link'   => '/a/m/AEGIS/projects/project/{{id}}',
            'name'   => 'dictionary.project',
            'fields' => [
                'reference',
                'name',
            ],
            'output' => [
                'reference',
                'name',
            ],
        ];
        $searches[VariantDocument::class] = [
            'icon'   => 'files',
            'link'   => '/a/m/Documents/document/document/{{document_id}}',
            'name'   => 'dictionary.document',
            'fields' => [
                'reference',
            ],
            'output' => [
                'reference',
                'name',
            ],
        ];
    }
}
