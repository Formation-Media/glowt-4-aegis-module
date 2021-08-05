<?php

namespace Modules\AEGIS\Http\Controllers\Store;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\AEGIS\Models\Scope;

class ScopesController extends Controller
{
    public function add(Request $request){
        $redirect = url('a/m/Aegis/scopes');
        $scope = new Scope();
        $scope->name = $request->name;
        $scope->added_by = \Auth::id();
        $scope->save();

        return redirect($redirect);
    }

    public function scope(Request $request, $id){
        $scope = Scope::find($id);
        $redirect = url('a/m/Aegis/scope/'.$scope->id);
        $scope->name = $request->name;
        $scope->added_by = \Auth::id();
        $scope->update();

        return redirect($redirect);
    }
}
