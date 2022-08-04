<?php

namespace Modules\AEGIS\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Training;

class TrainingController extends Controller
{
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
}
