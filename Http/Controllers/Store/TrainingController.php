<?php

namespace Modules\AEGIS\Http\Controllers\Store;

use App\Helpers\Dates;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Customer;
use Modules\AEGIS\Models\Training;

class TrainingController extends Controller
{
    public function add(Request $request)
    {
        return parent::validate(
            $request,
            [
                'customer_id'     => 'required|numeric|exists:m_aegis_scopes,id',
                'description'     => 'required',
                'duration_length' => 'required|numeric',
                'duration_period' => 'required|numeric',
                'end_date'        => 'required|date|after:start_date',
                'location'        => 'required',
                'name'            => 'required',
                'presentation'    => 'nullable',
                'presenter_id'    => 'required|numeric|exists:users,id',
                'start_date'      => 'required|date|before:end_date',
            ],
            function ($validated, Customer $customer) {
                $previous_training      = Training::orderBy('created_at', 'desc')->first();
                $next                   = $previous_training ? substr(explode('-', $previous_training->reference)[0], 2) + 1 : 1;
                $validated['reference'] = 'TC'.$next.'-'.$customer->reference.date('y').'Q'.Dates::quarter(time());
                $training               = Training::create($validated);
                return redirect($this->link_base.'training/'.$training->id);
            },
        );
    }
    public function training(Request $request, $id)
    {
        return parent::validate(
            $request,
            [
                'customer_id'     => 'required|numeric|exists:m_aegis_scopes,id',
                'description'     => 'required',
                'duration_length' => 'required|numeric',
                'duration_period' => 'required|numeric',
                'end_date'        => 'required|date|after:start_date',
                'location'        => 'required',
                'name'            => 'required',
                'presentation'    => 'nullable',
                'presenter_id'    => 'required|numeric|exists:users,id',
                'start_date'      => 'required|date|before:end_date',
            ],
            function ($validated) use ($id) {
                $training = Training::find($id);
                $training->update($validated);
                return redirect($this->link_base.'training/'.$training->id);
            },
        );
    }
}
