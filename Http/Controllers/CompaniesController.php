<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Company;

class CompaniesController extends Controller
{
    public function company(Request $request, int $id)
    {
        $company = Company::find($id);
        return parent::view(compact('company'));
    }
    public function index()
    {
        $permissions = \Auth::user()->feature_permissions('AEGIS', 'companies');
        return parent::view(compact('permissions'));
    }
}
