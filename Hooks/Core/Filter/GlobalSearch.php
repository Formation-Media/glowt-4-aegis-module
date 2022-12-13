<?php

namespace Modules\AEGIS\Hooks\Core\Filter;

use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\VariantDocument;
use Modules\Documents\Models\Document;

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
        // Replace documents search with Variant Document Search
        unset($searches[Document::class]);
        $searches[VariantDocument::class] = [
            'icon'   => 'files',
            'link'   => '/a/m/Documents/document/document/{{document_id}}',
            'name'   => 'dictionary.document',
            'fields' => [
                'm_documents_documents.name',
                'issue',
                'reference',
            ],
            'joins' => [
                ['m_documents_documents', 'id', 'document_id'],
            ],
            'output' => [
                'reference',
                'issue',
                'name',
            ],
        ];
    }
}
