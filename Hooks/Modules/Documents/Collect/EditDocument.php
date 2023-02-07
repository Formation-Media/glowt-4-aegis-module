<?php

namespace Modules\AEGIS\Hooks\Modules\Documents\Collect;

use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\ProjectVariant;
use Modules\AEGIS\Models\VariantDocument;
use Modules\Documents\Models\Category;
use Modules\Documents\Models\DocumentApprovalProcessItem;

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

        if (!isset($args['document']->author_company)) {
            $vd = VariantDocument::firstWhere('document_id', $args['document']->id);
            $args['document']->setMeta([
                'author_company' => $vd->project->company_id,
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
        if (isset($args['request']->submit)) {
            if (!$args['document']->approval_process->approval_process_stages->count()) {
                $author_company = Company::withTrashed()->find($args['document']->meta['author_company']);
                $author_prefix  = $author_company->abbreviation.'-'.$args['document']->created_by->getMeta('aegis.user-reference').'-';
                if ($previous_author_reference = \DB
                    ::table('m_documents_meta')
                    ->where('key', 'author_reference')
                    ->where('value', 'LIKE', $author_prefix.'%')
                    ->orderByRaw('CAST(SUBSTRING(`value`, '.(strlen($author_prefix) + 1).') AS UNSIGNED) DESC')
                    ->first()
                ) {
                    $previous_author_reference = $previous_author_reference->value;
                }
                if ($previous_approval_reference = DocumentApprovalProcessItem
                    ::where('reference', 'LIKE', $author_prefix.'%')
                    ->orderByRaw('CAST(SUBSTRING(`reference`, '.(strlen($author_prefix) + 1).') AS UNSIGNED) DESC')
                    ->first()
                ) {
                    $previous_approval_reference = $previous_approval_reference->reference;
                }
                if (!$previous_approval_reference && !$previous_author_reference) {
                    $previous_reference = $author_prefix.'0';
                } else {
                    $max = 0;
                    foreach ([
                        'previous_author_reference',
                        'previous_approval_reference',
                    ] as $key) {
                        if ($$key) {
                            $max = max($max, str_replace($author_prefix, '', $$key));
                        }
                    }
                    $previous_reference = $author_prefix.$max;
                }

                list($company_reference, $user, $increment) = explode('-', $previous_reference);
                $new_reference                              = implode(
                    '-',
                    [$company_reference, $user, str_pad(++$increment, 3, '0', STR_PAD_LEFT)]
                );
                $args['document']->setMeta('author_reference', $new_reference);
                $args['document']->save();
            }
        }
        $args['document']->save();
    }
}
