<?php

namespace Modules\AEGIS\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Modules\AEGIS\Models\Project;
use App\Models\User;

class ProjectsController extends Controller
{
    public function table_view($request){
        $actions       =array();
        $global_actions=array();
        $actions=array(
            array(
                'style'=>'primary',
                'name' =>'View',
                'uri'  =>'/a/m/Aegis/projects/project/{{id}}'
            ),
        );
        $global_actions = array(
            array(
                'action' =>'delete-project',
                'style' =>'danger',
                'title' =>'Delete'
            )
        );
        $row_structure=array(
            'actions'=>$actions,
            'data'=>array(
                'ID'=>array(
                    'columns'=>'id',
                    'display'=>false
                ),
                'Name'=>array(
                    'columns'     =>'name',
                    'default_sort'=>'asc',
                    'sortable'    =>true,
                ),
                'Type'=>array(
                    'columns'     =>'type',
                    'sortable'    =>true,
                ),
                'Added By'=>array(
                    'sortable'=>true,
                ),
                'Added at'=>array(
                    'columns' =>'created_at',
                    'sortable'=>true,
                    'class'   =>'\App\Helpers\Dates',
                    'method'  =>'datetime',
                ),
                'Updated at'=>array(
                    'columns' =>'updated_at',
                    'sortable'=>true,
                    'class'   =>'\App\Helpers\Dates',
                    'method'  =>'datetime',
                ),

            ),
        );
        return parent::to_ajax_table('Project',$row_structure,$global_actions,
            function ($query){
                return $query;
            },
            function ($in,$out){
                $project = Project::where('id', $in['id'])->first();
                $added_by = User::where('id',$project->added_by)->first();
                $out['Added By'] = $added_by->name;
                return $out;
            }
        );
    }

}
