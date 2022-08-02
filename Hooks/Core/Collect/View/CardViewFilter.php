<?php

namespace Modules\AEGIS\Hooks\Core\Collect\View;

use App\Models\User;
use Modules\AEGIS\Models\JobTitle;

class CardViewFilter
{
    public static function run($args)
    {
        $roles = [];
        if (!$args['route']['module'] && $args['route']['feature'] === 'users' && $args['route']['view'] === 'user') {
            $roles = JobTitle::whereIn('id', (array) User::find($args['route']['id'])->getMeta('aegis.discipline'))->formatted();
        }
        return view(
            'aegis::_hooks.card-view-filter',
            compact(
                'args',
                'roles',
            )
        )->render();
    }
}
