<?php

namespace Modules\Aegis\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AegisController extends \App\Http\Controllers\Controller
{
    // Ajax
    public function ajax_load(Request $request){

    }
    // Autocomplete
    public function ajax_autocomplete(Request $request){

    }
    // Form Storage
    public function store_add(Request $request){
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
