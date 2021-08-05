<?php

namespace Modules\AEGIS\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\AEGIS\Models\Scope;

class ScopesController extends Controller
{
    public function index()
    {
        return parent::view();
    }

    public function scope(Request $request, $id)
    {
        $scope = Scope::find($id);
        return parent::view(compact('scope'));
    }

    public function add(Request $request){

        return parent::view();
    }
}
