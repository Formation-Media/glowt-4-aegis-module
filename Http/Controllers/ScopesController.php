<?php

namespace Modules\AEGIS\Http\Controllers;

use Illuminate\Http\Request;
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
        $scope = Scope::findOrFail($id);
        return parent::view(compact('scope'));
    }

    public function add(Request $request){

        return parent::view();
    }
}
