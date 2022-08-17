<?php

namespace Modules\AEGIS\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Helpers\Training as HelpersTraining;
use Modules\AEGIS\Models\Customer;
use Modules\AEGIS\Models\Training;

class TrainingController extends Controller
{
    public function add_customer(Request $request)
    {
        return parent::validate(
            $request,
            [
                'customer' => 'required',
            ],
            function ($validated) {
                $customer = Customer::create([
                    'name' => $validated['customer'],
                ]);
                return [
                    'customer'  => $customer,
                    'reference' => HelpersTraining::next_reference($customer),
                ];
            },
        );
    }
    public function autocomplete_location(Request $request)
    {
        $return = array();
        if ($trainings = Training::search(
            array(
                'location'
            ),
            $request->term
        )->paged()) {
            foreach ($trainings as $training) {
                $return[] = array(
                    'value'   => $training->location,
                    'content' => $training->location,
                );
            }
        }
        return $return;
    }
    public function autocomplete_presentation(Request $request)
    {
        $return = array();
        if ($trainings = Training::search(
            array(
                'presentation'
            ),
            $request->term
        )->paged()) {
            foreach ($trainings as $training) {
                $return[] = array(
                    'value'   => $training->presentation,
                    'content' => $training->presentation,
                );
            }
        }
        return $return;
    }
    public function next_reference(Request $request)
    {
        return parent::validate(
            $request,
            [
                'customer' => 'required|numeric|exists:m_aegis_scopes,id',
            ],
            function ($validated, Customer $customer) {
                return HelpersTraining::next_reference($customer);
            },
        );
    }
}
