<?php

namespace Modules\AEGIS\Http\Controllers\Store;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\AEGIS\Models\Customer;

class CustomersController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            'reference' => 'required|max:3|unique:Modules\AEGIS\Models\Customer',
        ]);
        $customer            = new Customer();
        $customer->name      = $request->name;
        $customer->added_by  = \Auth::id();
        $customer->reference = strtoupper($request->reference);
        $customer->save();
        $redirect = url('a/m/AEGIS/customers/customer/'.$customer->id);
        return redirect($redirect);
    }

    public function customer(Request $request, $id)
    {
        $customer           = Customer::find($id);
        $redirect           = url('a/m/AEGIS/customers/customer/'.$customer->id);
        $customer->name     = $request->name;
        $customer->added_by = \Auth::id();
        $customer->update();
        return redirect($redirect);
    }
}
