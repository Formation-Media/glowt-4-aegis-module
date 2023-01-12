<?php

namespace Modules\AEGIS\View\CardView;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\AEGIS\Helpers\Icons;

class Project
{
    public function actions($result, Request $request, $row)
    {
        return [
            [
                'href'  => '/a/m/AEGIS/projects/project/'.$result->id,
                'name'  => 'dictionary.view',
            ],
        ];
    }
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
    public function filter(Builder $query, Request $request)
    {
        if (isset($request->dataset['customerId'])) {
            $query->where('scope_id', $request->dataset['customerId']);
        }
        return $query;
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
    public function status($result)
    {
        return 'status';
    }
    public function title($result)
    {
        return $result->name;
    }
}
