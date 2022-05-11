<?php

namespace Modules\AEGIS\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\JobTitle;

class UsersController extends Controller
{
    public function get_roles(Request $request)
    {
        return parent::validate(
            $request,
            [
                'id' => 'required|exists:users,id',
            ],
            function ($validated) {
                $roles = [];
                $user  = User::find($validated['id']);
                if ($role_ids = $user->getMeta('aegis.discipline')) {
                    $roles = JobTitle::select('id', 'name')->whereIn('id', $role_ids)->ordered()->get();
                }
                return $roles;
            },
        );
    }
}
