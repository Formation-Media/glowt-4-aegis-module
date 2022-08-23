<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Collect;

use Modules\AEGIS\Models\ProjectVariant;
use Modules\AEGIS\Models\VariantDocument;
use Modules\Documents\Models\Category;

class AddDocumentValidation
{
    public static function run($args)
    {
        if (isset($args['request']->aegis['project_variant'])) {
            $category        = Category::find($args['request']->category);
            $project_variant = ProjectVariant::find($args['request']->aegis['project_variant']);
            $reference       = $project_variant->project->reference.'/'.$category->prefix
                                .str_pad($args['request']->aegis['reference'], 2, '0', STR_PAD_LEFT);

            $issues = VariantDocument::where([
                'issue'     => $args['request']->aegis['issue'],
                'reference' => $reference,
            ]);

            if ($issues->count()) {
                $args['validator']->errors()->add(
                    'global_errors',
                    'aegis::messages.previous-issue-not-approved.title'
                );
            }
        }
    }
}
