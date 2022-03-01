<?php

namespace Modules\AEGIS\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Customer;

class ManagementController extends Controller
{
    public function add_customer(Request $request)
    {
        $redirect           = url('a/m/AEGIS/customers');
        $customer           = new Customer();
        $customer->name     = $request->name;
        $customer->added_by = \Auth::id();
        $customer->save();
        return redirect($redirect);
    }
}
