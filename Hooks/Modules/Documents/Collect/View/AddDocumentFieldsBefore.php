<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Collect\View;

use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\ProjectVariant;

class AddDocumentFieldsBefore
{
    public static function run($args)
    {
        $projects         = Project::ordered()->pluck('name', 'id')->toArray();
        $project_variants = null;
        $selected_variant = null;
        $selected_project = null;
        if (isset($_GET['project_variant'])) {
            $selected_variant = ProjectVariant::find($_GET['project_variant']);
            $selected_project = $selected_variant->project;
            if ($variants = $selected_project->variants) {
                foreach ($variants as $variant) {
                    $project_variants[$variant->id] = $variant->variant_number.' - '.$variant->name;
                }
            }
        }
        return view(
            'aegis::_hooks.add-document-fields-before',
            compact(
                'projects',
                'project_variants',
                'selected_project',
                'selected_variant',
            )
        );
    }
}
