<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Collect;

use Modules\AEGIS\Models\ProjectVariant;
use Modules\AEGIS\Models\VariantDocument;
use Modules\Documents\Models\Category;

class EditDocument
{
    public static function run($args)
    {
        $category = Category::find($args['request']->category);
        if ($category->prefix === 'FBL') {
            $args['document']->setMeta([
                'feedback_list_type_id' => $args['request']->aegis['feedback-list-type'],
                'final_feedback_list'   => $args['request']->aegis['final-feedback-list'],
            ]);
        } else {
            $args['document']->unsetMeta([
                'feedback_list_type_id',
                'final_feedback_list',
            ]);
        }
        $args['document']->setMeta([
            'author_role' => $args['request']->aegis['author-role'],
        ]);
        $args['document']->save();
        if (isset($args['request']->aegis['project_variant']) || isset($args['request']->aegis['reference'])) {
            $updates = [];
            if (isset($args['request']->aegis['project_variant'])) {
                $updates['variant_id'] = $args['request']->aegis['project_variant'];
            }
            if (isset($args['request']->aegis['reference'])) {
                $project_variant      = ProjectVariant::find($updates['variant_id']);
                $updates['reference'] = $project_variant->reference.'/'.$args['document']->category->prefix
                    .str_pad($args['request']->aegis['reference'], 2, '0', STR_PAD_LEFT);
            }

            $vd = VariantDocument::updateOrCreate(
                ['document_id' => $args['document']->id],
                $updates
            );
            $vd->document->log('phrases.updated', ['what' => 'documents::phrases.document-details']);
        }
    }
}
