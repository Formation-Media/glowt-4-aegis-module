<?php

namespace Modules\AEGIS\Http\Controllers\Store;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\AEGIS\Models\Scope;

class ScopesController extends Controller
{
    public function add(Request $request){
        $scope = new Scope();
        $scope->name = $request->name;
        $scope->added_by = \Auth::id();
        $scope->save();
        $redirect = url('a/m/AEGIS/scopes/scope/'.$scope->id);
        return redirect($redirect);
    }

    public function scope(Request $request, $id){
        $scope = Scope::find($id);
        $redirect = url('a/m/AEGIS/scopes/scope/'.$scope->id);
        $scope->name = $request->name;
        $scope->added_by = \Auth::id();
        $scope->update();

        return redirect($redirect);
    }
}
