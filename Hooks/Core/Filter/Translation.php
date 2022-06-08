<?php

namespace Modules\AEGIS\Hooks\Core\Filter;

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
    }
}
