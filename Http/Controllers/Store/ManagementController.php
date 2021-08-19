<?php

namespace Modules\AEGIS\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Scope;

class ManagementController extends Controller
{
    public function add_scope(Request $request){
        $redirect = url('a/m/AEGIS/scopes');
        $scope = new Scope();
        $scope->name = $request->name;
        $scope->added_by = \Auth::id();
        $scope->save();
        return redirect($redirect);
    }
}
