<?php

namespace Modules\AEGIS\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\AEGIS\Models\JobTitle;
use Modules\AEGIS\Models\UserGrade;

class ManagementController extends Controller
{
    // Ajax
    public function add_job_title(Request $request){
        $grade        =new JobTitle();
        $grade->name  =$request->name;
        $grade->status=$request->status??0;
        $grade->save();
        return $grade;
    }
    public function add_user_grade(Request $request){
        $grade        =new UserGrade();
        $grade->name  =$request->name;
        $grade->status=$request->status??0;
        $grade->save();
        return $grade;
    }
    public function delete_job_title(Request $request){
        $grade=JobTitle::findOrFail($request->id);
        $grade->delete();
        return true;
    }
    public function delete_user_grade(Request $request){
        $grade=UserGrade::findOrFail($request->id);
        $grade->delete();
        return true;
    }
    public function table_job_titles(Request $request){
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
            if($permissions['delete']){
                $row_structure['actions'][]=array(
                    'class'=>'js-delete-job-title',
                    'id'   =>'{{id}}',
                    'style'=>'danger',
                    'name' =>'Delete'
                );
            }
        }
        return parent::to_ajax_table('JobTitle',$row_structure,array(),function($query){
            return $query->orderBy('name');
        });
    }
    public function table_user_grades(Request $request){
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
            if($permissions['delete']){
                $row_structure['actions'][]=array(
                    'class'=>'js-delete-user-grade',
                    'id'   =>'{{id}}',
                    'style'=>'danger',
                    'name' =>'Delete'
                );
            }
        }
        return parent::to_ajax_table('UserGrade',$row_structure,array(),function($query){
            return $query->orderBy('name');
        });
    }
}
