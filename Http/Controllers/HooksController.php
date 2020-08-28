<?php

namespace Modules\Aegis\Http\Controllers;

use App\Http\Controllers\HooksController as Hooks;
use Modules\Aegis\Models\Supplier;

class HooksController extends \App\Http\Controllers\Controller
{
    public static function collect_add_competency_fields($args){
        $supplier_data=Supplier::all();
        $suppliers    =array();
        if(sizeof($supplier_data)){
            foreach($supplier_data as $supplier){
                $suppliers[$supplier->id]=$supplier->name;
            }
        }
        return view(
            'aegis::hooks.add-competency-fields',
            compact(
                'suppliers'
            )
        );
    }
    public static function collect_set_up($args){
        return view('aegis::hooks.set-up-page');
    }
}
