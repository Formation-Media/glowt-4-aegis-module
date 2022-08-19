<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Customer;

class CustomersController extends Controller
{
    public function index()
    {
        return parent::view();
    }

    public function customer(Request $request, $id)
    {
        $customer         = Customer::findOrFail($id);
        $customer_details = [
            'dictionary.reference' => $customer->reference,
            'phrases.created-by'   => User::withTrashed()->find($customer->added_by)->name,
        ];
        return parent::view(compact(
            'customer',
            'customer_details'
        ));
    }

    public function add(Request $request)
    {
        return parent::view();
    }
}
