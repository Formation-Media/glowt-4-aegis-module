<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Collect;

use Modules\AEGIS\Models\VariantDocument;

class SubmitDocumentValidation
{
    public static function run($args)
    {
        if ($args['validated']['approval_process_items']) {
            $variant_document = VariantDocument::firstWhere('document_id', $args['document']->id);
            if ($variant_document->issue === 1) {
                $users = [];
                foreach ($args['validated']['approval_process_items'] as $stages) {
                    foreach ($stages as $item) {
                        if ($item['approver-autocomplete']) {
                            if (in_array($item['approver'], $users)) {
                                $args['validator']->errors()->add(
                                    'global_errors',
                                    'aegis::messages.document.duplicate-approval-item-users',
                                );
                                break;
                            }
                            $users[] = $item['approver'];
                        }
                    }
                }
            }
        }
    }
}
