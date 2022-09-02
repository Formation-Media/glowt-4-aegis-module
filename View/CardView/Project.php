<?php

namespace Modules\AEGIS\View\CardView;

use Modules\AEGIS\Helpers\Icons;

class Project
{
    public function details($result)
    {
        return [
            'dictionary.company' => [
                'icon'  => Icons::company(),
                'value' => $result->company->name,
            ],
            'dictionary.type' => [
                'icon'  => Icons::type(),
                'value' => $result->type->name,
            ],
            'aegis::dictionary.phases' => [
                'icon'  => Icons::phase(),
                'value' => $result->phases->count(),
            ],
        ];
    }
    public function href($result)
    {
        return '/a/m/AEGIS/projects/project/'.$result->id;
    }
    public function reference($result)
    {
        return $result->reference;
    }
    public function search()
    {
        return [
            'name',
            'reference',
        ];
    }
    public function sort()
    {
        return [
            'reference' => [
                'translation' => 'dictionary.reference',
                'default'     => 'desc',
            ],
            'title' => [
                'translation' => 'dictionary.title',
            ],
        ];
    }
    public function title($result)
    {
        return $result->name;
    }
}
