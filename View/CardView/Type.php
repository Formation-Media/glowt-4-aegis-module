<?php

namespace Modules\AEGIS\View\CardView;

use App\Helpers\FontAwesome;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class Type
{
    public function actions($result, Request $request, $row)
    {
        $actions = [
            [
                'href'  => '/a/m/AEGIS/management/project-type/'.$result->id,
                'name'  => 'dictionary.edit',
            ],
        ];
        if ($result->children()->count()) {
            $actions[] = [
                'href'  => '/a/m/AEGIS/management/project-types/'.$result->id,
                'name'  => 'dictionary.children',
            ];
        }
        return $actions;
    }
    public function details($result)
    {
        return [
            'dictionary.children' => [
                'icon'  => 'list-tree',
                'value' => $result->children()->count(),
            ],
            'dictionary.added' => [
                'icon'  => FontAwesome::datetime_icon($result->created_at->format('Y-m-d H:i:s'), true),
                'value' => $result->nice_created_at,
            ],
            'dictionary.updated' => [
                'icon'  => FontAwesome::datetime_icon($result->updated_at->format('Y-m-d H:i:s'), true),
                'value' => $result->nice_updated_at,
            ],
        ];
    }
    public function filter(Builder $query, Request $request)
    {
        if (is_null($request->_GET['id'])) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $request->_GET['id']);
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
        ];
    }
    public function sort()
    {
        return [
            'name' => [
                'translation' => 'dictionary.name',
                'default'     => 'asc',
            ],
        ];
    }
    public function title($result)
    {
        return $result->name;
    }
}
