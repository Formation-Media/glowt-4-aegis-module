<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Scope;

class ScopesController extends Controller
{
    public function index()
    {
        return parent::view();
    }

    public function scope(Request $request, $id)
    {
        $scope         = Scope::findOrFail($id);
        $scope_details = [
            __('dictionary.reference') => $scope->reference,
            __('phrases.created-by')   => User::find($scope->added_by)->name,
        ];
        return parent::view(compact(
            'scope',
            'scope_details'
        ));
    }

    public function add(Request $request)
    {
        return parent::view();
    }
}
