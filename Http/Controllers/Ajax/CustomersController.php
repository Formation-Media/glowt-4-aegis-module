<?php

namespace Modules\AEGIS\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\Toast;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Customer;

class CustomersController extends Controller
{
    public function add_customer($request)
    {
        $i         = 0;
        $reference = strtoupper(substr($request->name, 0, 3));
        while (Customer::where('reference', $reference.str_pad(++$i, 3, '0', STR_PAD_LEFT))->count() > 0) {
            // When the loop stops we've got the reference #
            continue;
        }
        return Customer::create([
            'added_by'  => \Auth::id(),
            'name'      => $request->name,
            'reference' => $reference.str_pad($i, 3, '0', STR_PAD_LEFT),
        ]);
    }
    public function autocomplete_customers($request)
    {
        $return = array();
        if ($customers = Customer::search(
            array(
                'name'
            ),
            '%'.$request->term.'%'
        )->paged()) {
            foreach ($customers as $customer) {
                $return[] = array(
                    'data'    => $customer,
                    'value'   => $customer->id,
                    'content' => $customer->name,
                );
            }
        }
        return $return;
    }
    public function delete_customer(Request $request)
    {
        $user = \Auth::user();
        if ($request->ids) {
            $customers = array();
            if ($customers = Customer::whereIn('id', $request->ids)->get()) {
                foreach ($customers as $customer) {
                    $names[] = $customer->name;
                    $customer->delete();
                }
            }
            if ($names) {
                $user->notify(new Toast(
                    'Delete Customers',
                    'Successfully deleted '.number_format(count($names)).' customers: '.implode(', ', $names)
                ));
            }
        } else {
            $user->notify(new Toast(
                'Delete Customers',
                'No customers were selected for deletion.'
            ));
        }
        return true;
    }
    public function disable_customer(Request $request)
    {
        $user = \Auth::user();
        if ($request->ids) {
            $names = array();
            if ($customers = Customer::whereIn('id', $request->ids)->get()) {
                foreach ($customers as $customer) {
                    $names[]          = $customer->name;
                    $customer->status = false;
                    $customer->save();
                }
            }
            if ($names) {
                $user->notify(new Toast(
                    'Disabled Customers',
                    'Successfully disabled '.number_format(count($names)).' customers: '.implode(', ', $names)
                ));
            }
        } else {
            $user->notify(new Toast('Disabled Customers', 'No Customers were selected for disabling.'));
        }
        return true;
    }
    public function enable_customer(Request $request)
    {
        $user = \Auth::user();
        if ($request->ids) {
            $names = array();
            if ($customers = Customer::whereIn('id', $request->ids)->get()) {
                foreach ($customers as $customer) {
                    $names[]          = $customer->name;
                    $customer->status = false;
                    $customer->save();
                }
            }
            if ($names) {
                $user->notify(new Toast(
                    'Disabled Customers',
                    'Successfully disabled '.number_format(count($names)).' customers: '.implode(', ', $names)
                ));
            }
        } else {
            $user->notify(new Toast('Disabled Customers', 'No customers were selected for disabling.'));
        }
        return true;
    }

    public function table_view($request)
    {
        $user           = \Auth::user();
        $global_actions = array();
        $actions        = array(
            array(
                'style' => 'primary',
                'name'  => ___('View'),
                'href'  => '/a/m/AEGIS/customers/customer/{{id}}',
            ),
        );
        if ($user->has_role('core::Administrator') || $user->has_role('core::Manager')) {
            $global_actions = array(
                array(
                    'action' => 'enable-customer',
                    'icon'   => 'square-check',
                    'style'  => 'success',
                    'title'  => ___('dictionary.enable'),
                ),
                array(
                    'action' => 'disable-customer',
                    'icon'   => 'square-xmark',
                    'style'  => 'warning',
                    'title'  => ___('dictionary.disable'),
                ),
                array(
                    'action' => 'delete-customer',
                    'icon'   => 'square-xmark',
                    'style'  => 'danger',
                    'title'  => ___('dictionary.delete'),
                ),
            );
        }
        $row_structure = array(
            'actions' => $actions,
            'data'    => array(
                'ID' => array(
                    'columns' => 'id',
                    'display' => false,
                ),
                ___('dictionary.name') => array(
                    'columns'      => 'name',
                    'default_sort' => 'asc',
                    'sortable'     => true,
                ),
                ___('dictionary.reference') => array(
                    'columns'      => 'reference',
                    'default_sort' => 'asc',
                    'sortable'     => true,
                ),
                ___('phrases.added-by') => array(
                    'sortable' => true,
                ),
                ___('phrases.added-at') => array(
                    'columns'  => 'created_at',
                    'sortable' => true,
                    'class'    => '\App\Helpers\Dates',
                    'method'   => 'datetime',
                ),
                ___('phrases.updated-at') => array(
                    'columns'  => 'updated_at',
                    'sortable' => true,
                    'class'    => '\App\Helpers\Dates',
                    'method'   => 'datetime',
                ),
            ),
            'status' => true
        );
        return parent::to_ajax_table(
            'Customer',
            $row_structure,
            $global_actions,
            function ($query) {
                return $query;
            },
            function ($in, $out) {
                $customer                     = Customer::where('id', $in['id'])->first();
                $added_by                     = User::where('id', $customer->added_by)->first();
                $out[___('phrases.added-by')] = $added_by->name;
                return $out;
            }
        );
    }
}
