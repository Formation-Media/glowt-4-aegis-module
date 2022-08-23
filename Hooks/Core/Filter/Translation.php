<?php

namespace Modules\AEGIS\Hooks\Core\Filter;

use Modules\AEGIS\Models\VariantDocument;

class Translation
{
    public static function run(&$translation_string, $module, $replacements = null)
    {
        $path = request()->path();
        if (in_array($path, [
            'a/management',
            'a/m/Documents/categories',
        ])
            || str_starts_with($path, 'a/m/Documents/categories/category/')
            || str_starts_with($path, 'a/m/Documents/document/')
        ) {
            switch ($translation_string) {
                case 'dictionary.category':
                    $translation_string = 'aegis::phrases.document-type';
                    break;
                case 'dictionary.type':
                    $translation_string = 'aegis::phrases.document-type';
                    break;
                case 'dictionary.types':
                    $translation_string = 'aegis::phrases.document-types';
                    break;
            }
        }
        if (isset($replacements['document_id']) && !isset($replacements['translated'])) {
            if ($variant_document = VariantDocument::firstWhere('document_id', $replacements['document_id'])) {
                switch ($translation_string) {
                    case 'documents::messages.approved-approval-item':
                        $replacements['document'] = $variant_document->reference.': '.$replacements['document'];
                        break;
                    case 'documents::messages.denied-approval-item':
                        $replacements['document'] = $variant_document->reference.': '.$replacements['document'];
                        break;
                    case 'documents::messages.submitted-for-approval':
                        $replacements['document'] = $variant_document->reference.': '.$replacements['document'];
                        break;
                    case 'documents::messages.submitted-for-approval-to':
                        $replacements['document'] = $variant_document->reference.': '.$replacements['document'];
                        break;
                    case 'messages.updated.single.message':
                        $replacements['item'] = $variant_document->reference.': '.$replacements['item'];
                        break;
                }
                $replacements['translated'] = true;

                $translation_string = ___($translation_string, $replacements);
            }
        }
    }
}
