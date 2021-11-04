<?php

namespace Modules\AEGIS\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\AEGIS\Models\Company;

class CompaniesController extends Controller
{
    public function company(Request $request, $id)
    {
        $redirect = $this->link_base.'company/'.$id;
        if (!$company = Company::find($id)) {
            return redirect($this->link_base);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect($redirect)
                ->withErrors($validator)
                ->withInput();
        }
        $company->name   = $request->name;
        $company->status = $request->status ?? 0;
        $company->save();
        return redirect($redirect);
    }
}
