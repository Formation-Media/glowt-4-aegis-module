<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Customer;
use Modules\AEGIS\Models\Scope;

class CustomersController extends Controller
{
    public function index()
    {
        $customers = Scope::getOrdered()->pluck('name', 'id');
        return parent::view(compact(
            'customers'
        ));
    }

    public function customer(Request $request, $id)
    {
        $customer         = Customer::findOrFail($id);
        $customers        = Scope::getOrdered()->pluck('name', 'id');
        $customer_details = [
            'dictionary.reference' => $customer->reference,
            'phrases.created-by'   => User::withTrashed()->find($customer->added_by)->name,
        ];
        $page_menu = [
            array(
                'href'  => '/a/m/AEGIS/projects/add/'.$customer->id,
                'icon'  => 'file-plus',
                'title' => ___('Add Project to Customer'),
            ),
            [
                'class' => 'js-merge',
                'd-id'  => $customer->id,
                'icon'  => 'merge',
                'title' => 'dictionary.merge',
            ],
        ];
        $tabs = [
            ['name' => ___('dictionary.details')],
            ['name' => ___('dictionary.projects')],
        ];
        return parent::view(compact(
            'customer',
            'customer_details',
            'customers',
            'page_menu',
            'tabs',
        ));
    }

    public function add(Request $request)
    {
        return parent::view();
    }
}
