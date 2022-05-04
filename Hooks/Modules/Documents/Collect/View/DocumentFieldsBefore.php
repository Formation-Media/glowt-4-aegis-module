<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Collect\View;

use App\Helpers\Translations;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\VariantDocument;

class DocumentFieldsBefore
{
    public static function run($document)
    {
        $projects         = Project::ordered()->pluck('name', 'id')->toArray();
        $document_variant = VariantDocument::where('document_id', $document->id)->first();
        $yes_no           = Translations::yes_no();
        if ($document_variant) {
            $selected_variant = $document_variant->project_variant;
            $selected_project = $document_variant->project_variant->project;
            if ($variants = $selected_project->variants) {
                foreach ($variants as $variant) {
                    $project_variants[$variant->id] = $variant->variant_number.' - '.$variant->name;
                }
            }
        } else {
            $selected_variant = null;
            $selected_project = null;
            $project_variants = [];
        }
        return view(
            'aegis::_hooks.add-document-fields-before',
            compact(
                'document',
                'projects',
                'project_variants',
                'selected_project',
                'selected_variant',
            )
        );
    }
}
