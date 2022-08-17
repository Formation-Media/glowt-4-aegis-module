<?php

namespace Modules\AEGIS\Helpers;

use App\Helpers\Dates;
use Modules\AEGIS\Models\Customer;
use Modules\AEGIS\Models\Training as ModelsTraining;

class Training
{
    public static function periods($period = null)
    {
        $periods = array(
            1 => 'aegis::dictionary.minutes',
            2 => 'aegis::dictionary.hours',
            3 => 'dictionary.days',
            4 => 'dictionary.weeks',
        );
        if ($period === null) {
            return array_map('___', $periods);
        } elseif (array_key_exists($period, $periods)) {
            return ___($periods[$period]);
        }
        return false;
    }
    public static function next_reference(Customer $customer)
    {
        $previous_training = ModelsTraining::orderBy('created_at', 'desc')->first();
        $next              = $previous_training ? substr(explode('-', $previous_training->reference)[0], 2) + 1 : 1;
        return 'TC'.$next.'-'.$customer->reference.date('y').'Q'.Dates::quarter(time());
    }
}
