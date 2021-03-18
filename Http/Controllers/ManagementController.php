<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Company;

class ManagementController extends Controller
{
    public function job_titles(Request $request){
        return parent::view();
    }
    public function user_grades(Request $request){
        return parent::view();
    }
}
