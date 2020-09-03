<?php

namespace Modules\Aegis\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Aegis\Models\Supplier;

class SuppliersController extends Controller
{
    // Ajax
    public function ajax_add_supplier(Request $request){
        $supplier        =new Supplier();
        $supplier->name  =$request->name;
        $supplier->status=$request->status??0;
        $supplier->save();
        return $supplier;
    }
    public function ajax_table_suppliers(Request $request){
        $row_structure=array(
            'data'=>array(
                'Name'=>array(
                    'columns'     =>'name',
                    'default_sort'=>'asc',
                    'sortable'    =>true,
                ),
                'Added'=>array(
                    'columns' =>'created_at',
                    'sortable'=>true,
                    'class'   =>'\App\Helpers\Dates',
                    'method'  =>'datetime',
                ),
                'Updated'=>array(
                    'columns' =>'updated_at',
                    'sortable'=>true,
                    'class'   =>'\App\Helpers\Dates',
                    'method'  =>'datetime',
                ),
            )
        );
        return parent::to_ajax_table('Supplier',$row_structure,array(),function($query){
            return $query->orderBy('name');
        });
    }
    // Autocomplete
    public function ajax_autocomplete(Request $request){

    }
    // Form Storage
    public function store_add(Request $request){
    }
    // Views
    public function index(){
        return parent::view();
    }
}
