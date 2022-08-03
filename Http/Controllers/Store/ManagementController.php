<?php

namespace Modules\AEGIS\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Customer;
use Modules\AEGIS\Models\Type;

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
    public function project_type(Request $request, $id)
    {
        return parent::validate(
            array_merge(
                $request->all(),
                [
                    'id' => $id,
                ]
            ),
            [
                'id'        => 'required|exists:m_aegis_types,id',
                'name'      => 'required',
                'parent_id' => 'nullable|exists:m_aegis_types,id',
            ],
            function ($validated, Type $type) {
                // Do what you would usually when the validation passes
                $type->update($validated);
                return redirect($this->link_base.'project-type/'.$type->id);
            },
        );
    }
}
