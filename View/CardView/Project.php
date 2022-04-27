<?php

namespace Modules\AEGIS\View\CardView;

use App\Helpers\FontAwesome;

class Project
{
    public function details($result)
    {
        return [
            [
                'icon'  => 'building',
                'label' => 'dictionary.company',
                'value' => $result->company->name,
            ],
            [
                'icon'  => 'building',
                'label' => 'dictionary.type',
                'value' => $result->type->name,
            ],
            [
                'icon'  => 'user',
                'label' => 'phrases.added-by',
                'value' => $result->user->name,
            ],
            [
                'icon'  => FontAwesome::datetime_icon($result->created_at->format('Y-m-d H:i:s'), true),
                'label' => 'dictionary.updated',
                'value' => $result->nice_created_at,
            ],
            [
                'icon'  => FontAwesome::datetime_icon($result->updated_at->format('Y-m-d H:i:s'), true),
                'label' => 'dictionary.updated',
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
