<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Company;

class ManagementController extends Controller
{
    public function user_grades(Request $request){
        return parent::view();
    }
}
