<?php

namespace Modules\AEGIS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
    public function ajax_delete_company(Request $request){
        $company=Company::findOrFail($request->id);
        $company->delete();
        return true;
    }
    public function ajax_table_companies(Request $request){
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
    // Autocomplete
    public function ajax_autocomplete(Request $request){

    }
    // Form Storage
    public function store_company(Request $request,$id){
        $redirect=$this->link_base.'company/'.$id;
        if(!$company=Company::find($id)){
            return redirect($this->link_base);
        }
        $validator=Validator::make($request->all(),[
            'name'=>'required',
        ]);
        if($validator->fails()){
            return redirect($redirect)
                        ->withErrors($validator)
                        ->withInput();
        }
        $company->name  =$request->name;
        $company->status=$request->status??0;
        $company->save();
        return redirect($redirect);
    }
    // Views
    public function company(Request $request,int $id){
        $company=Company::find($id);
        return parent::view(compact('company'));
    }
    public function index(){
        $permissions=\Auth::user()->feature_permissions('AEGIS','companies');
        return parent::view(compact('permissions'));
    }
}
