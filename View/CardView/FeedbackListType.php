<?php

namespace Modules\AEGIS\View\CardView;

class FeedbackListType
{
    public function sort()
    {
        return [
            'reference' => [
                'translation' => 'dictionary.reference',
                'default'     => 'asc',
            ],
        ];
    }
    public function title($result)
    {
        return $result->reference;
    }
    public function subtitle($result)
    {
        return $result->name;
    }
}
