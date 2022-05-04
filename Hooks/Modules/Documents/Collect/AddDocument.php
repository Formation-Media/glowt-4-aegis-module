<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Collect;

use Modules\AEGIS\Models\ProjectVariant;
use Modules\AEGIS\Models\VariantDocument;
use Modules\Documents\Models\Category;

class AddDocument
{
    public static function run($args)
    {
        if (isset($args['request']->aegis['project_variant'])) {
            $category = Category::find($args['request']->category);
            if ($category->prefix === 'FBL') {
                $args['new_document']->setMeta([
                    'feedback_list_type_id' => $args['request']->aegis['feedback-list-type'],
                    'final_feedback_list'   => $args['request']->aegis['final-feedback-list'],
                ]);
            }
            $variant_document              = new VariantDocument();
            $variant_document->document_id = $args['new_document']->id;
            $variant_document->variant_id  = $args['request']->aegis['project_variant'];
            $project_variant               = ProjectVariant::find($args['request']->aegis['project_variant']);
            $variant_document->reference   = $project_variant->reference.'/'.$category->prefix
                                                .str_pad($args['request']->aegis['reference'], 2, '0', STR_PAD_LEFT);
            $issue                         = VariantDocument::where('reference', $variant_document->reference)->count();
            $variant_document->issue       = $issue + 1;
            $variant_document->save();
            $args['new_document']->setMeta([
                'author_company' => $project_variant->project->company_id,
                'author_role'    => $args['request']->aegis['author-role'],
            ]);
            $args['new_document']->save();
        }
    }
}
