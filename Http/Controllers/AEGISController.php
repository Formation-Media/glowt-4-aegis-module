<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AEGISController extends \App\Http\Controllers\Controller
{
    // Ajax
    public function ajax_load(Request $request){

    }
    // Autocomplete
    public function ajax_autocomplete(Request $request){

    }
    // Views
    public function add(){
        return parent::view();
    }
    // Helpers
    public static function user_types(){
        return array(
            1=>'Associate',
            2=>'Employee'
        );
    }
}
