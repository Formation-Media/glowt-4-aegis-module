<?php

namespace Modules\AEGIS\View\CardView;

use App\Helpers\FontAwesome;

class Project
{
    public function details($result)
    {
        return [
            'dictionary.company' => [
                'icon'  => 'building',
                'value' => $result->company->name,
            ],
            'dictionary.type' => [
                'icon'  => 'building',
                'value' => $result->type->name,
            ],
            'phrases.added-by' => [
                'icon'  => 'user',
                'value' => $result->user->name,
            ],
            'dictionary.updated' => [
                'icon'  => FontAwesome::datetime_icon($result->created_at->format('Y-m-d H:i:s'), true),
                'value' => $result->nice_created_at,
            ],
            'dictionary.updated' => [
                'icon'  => FontAwesome::datetime_icon($result->updated_at->format('Y-m-d H:i:s'), true),
                'value' => $result->nice_updated_at,
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
