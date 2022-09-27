<?php

namespace Modules\AEGIS\View\CardView;

use App\Helpers\FontAwesome;
use App\Notifications\Toast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\AEGIS\Helpers\Icons;
use Modules\AEGIS\Models\Type as ModelsType;

class Type
{
    public static function action_delete($validated)
    {
        if ($validated['ids']) {
            $names = array();
            if ($types = ModelsType::whereIn('id', $validated['ids'])->get()) {
                foreach ($types as $type) {
                    $names[] = $type->name;
                    $type->delete();
                }
            }
            if ($names) {
                $message = [
                    'messages.delete.success',
                    [
                        'count' => number_format(count($names)),
                        'item'  => 'aegis::phrases.project-type',
                    ],
                ];
            }
        } else {
            $message = [
                'messages.delete.error.nothing-selected',
                [
                    'item' => 'aegis::phrases.project-type',
                ],
            ];
        }
        \Auth::User()->notify(new Toast(
            ['messages.delete.title', ['item' => 'aegis::phrases.project-type']],
            $message
        ));
        return true;
    }
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
            'dictionary.companies' => [
                'icon'  => Icons::company(),
                'value' => $result->companies->pluck('name')->toArray(),
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
    public function global_actions()
    {
        return [
            [
                'action' => 'delete',
                'style'  => 'danger',
                'title'  => 'dictionary.delete',
            ],
        ];
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
