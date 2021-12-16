<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Http\Controllers\Controller;

class AEGISController extends Controller
{
    // Views
    public function add()
    {
        return parent::view();
    }
    // Helpers
    public static function user_types()
    {
        return array(
            1 => 'Associate',
            2 => 'Employee',
        );
    }
}
