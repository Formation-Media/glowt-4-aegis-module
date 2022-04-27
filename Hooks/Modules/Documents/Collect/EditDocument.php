<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Collect;

use Modules\AEGIS\Models\VariantDocument;

class EditDocument
{
    public static function run($args)
    {
        if (isset($args['request']->aegis['project_variant']) || isset($args['request']->aegis['documentreference'])) {
            $updates = [];
            if (isset($args['request']->aegis['project_variant'])) {
                $updates['variant_id'] = $args['request']->aegis['project_variant'];
            }
            if (isset($args['request']->aegis['documentreference'])) {
                $updates['reference'] = $args['request']->aegis['documentreference'];
            }

            $vd = VariantDocument::updateOrCreate(
                ['document_id' => $args['document']->id],
                $updates
            );
            $vd->document->log('phrases.updated', ['what' => 'documents::phrases.document-details']);
        }
    }
}
