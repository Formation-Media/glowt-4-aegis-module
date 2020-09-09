<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Company;

class CompaniesController extends Controller
{
    // Ajax
    public function ajax_add_company(Request $request){
        $company        =new Company();
        $company->name  =$request->name;
        $company->status=$request->status??0;
        $company->save();
        return $company;
    }
    public function ajax_table_companies(Request $request){
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
        return parent::to_ajax_table('Company',$row_structure,array(),function($query){
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
