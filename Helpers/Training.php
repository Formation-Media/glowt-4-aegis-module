<?php

namespace Modules\AEGIS\Helpers;

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
}
