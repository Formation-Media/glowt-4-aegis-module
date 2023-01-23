<?php

namespace Modules\AEGIS\Http\Controllers\Store;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Modules\AEGIS\Models\Customer;

class CustomersController extends Controller
{
    public function add(Request $request)
    {
        return parent::validate(
            $request,
            [
                'name'      => 'required',
                'reference' => 'required|unique:Modules\AEGIS\Models\Customer',
            ],
            function ($validated) {
                $customer            = new Customer();
                $customer->name      = $validated['name'];
                $customer->added_by  = \Auth::id();
                $customer->reference = strtoupper($validated['reference']);
                $customer->save();
                $redirect = url('a/m/AEGIS/customers/customer/'.$customer->id);
                return redirect($redirect);
            },
        );
    }

    public function customer(Request $request, $id)
    {
        return parent::validate(
            $request,
            [
                'name'      => 'required',
                'reference' => [
                    'required',
                    Rule::unique('m_aegis_scopes')->ignore($id),
                ],
            ],
            function ($validated) use ($id) {
                $customer           = Customer::find($id);
                $redirect           = url('a/m/AEGIS/customers/customer/'.$customer->id);
                $customer->name     = $validated['name'];
                $customer->added_by = \Auth::id();
                $customer->reference = strtoupper($validated['reference']);
                $customer->update();
                return redirect($redirect);
            },
        );
    }
}
