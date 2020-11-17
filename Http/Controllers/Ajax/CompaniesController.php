<?php

namespace Modules\AEGIS\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\Company;

class CompaniesController extends Controller
{
    // Ajax
    public function add_company(Request $request){
        $company        =new Company();
        $company->name  =$request->name;
        $company->status=$request->status??0;
        $company->save();
        return $company;
    }
    public function delete_company(Request $request){
        $company=Company::findOrFail($request->id);
        $company->delete();
        return true;
    }
    public function table_companies(Request $request){
        $permissions  =\Auth::user()->feature_permissions('AEGIS','companies');
        $row_structure=array(
            'actions'=>array(),
            'data'=>array(
                'id'=>array(
                    'columns'=>'id',
                    'display'=>false
                ),
                'Name'=>array(
                    'columns'     =>'name',
                    'default_sort'=>'asc',
                    'sortable'    =>true,
                ),
                'Status'=>array(
                    'columns'=>'status',
                    'from_boolean'=>array(
                        'Enabled',
                        'Disabled'
                    ),
                    'sortable'=>true,
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
        if($permissions){
            if($permissions['company']){
                $row_structure['actions'][]=array(
                    'style'=>'primary',
                    'name' =>'Edit',
                    'uri'  =>$this->link_base.'company/{{id}}'
                );
            }
            if($permissions['delete']){
                $row_structure['actions'][]=array(
                    'class'=>'js-delete-company',
                    'id'   =>'{{id}}',
                    'style'=>'danger',
                    'name' =>'Delete'
                );
            }
        }
        return parent::to_ajax_table('Company',$row_structure,array(),function($query){
            return $query->orderBy('name');
        });
    }
}
