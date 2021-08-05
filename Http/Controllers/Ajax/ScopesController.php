<?php

namespace Modules\AEGIS\Http\Controllers\Ajax;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Models\User;
use Modules\AEGIS\Models\Scope;

class ScopesController extends Controller
{

    public function autocomplete_scopes($request){
        $return=array();
        if($scopes=Scope::search(
            array(
                'name'
            ),
            '%'.$request->term.'%'
        )->paged()
        ){
            foreach($scopes as $scope){
                $return[]=array(
                    'data'   =>$scope,
                    'value'   =>$scope->id,
                    'content'=>$scope->name
                );
            }
        }
        return $return;
    }

    public function table_view($request){
        $actions       =array();
        $global_actions=array();
        $actions=array(
            array(
                'style'=>'primary',
                'name' =>'View',
                'uri'  =>'/a/m/Aegis/scopes/scope/{{id}}'
            ),
        );
        $global_actions = array(
            array(
                'action' =>'delete-scope',
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
        return parent::to_ajax_table('scope',$row_structure,$global_actions,
            function ($query){
                return $query;
            },
            function ($in,$out){
                $scope = Scope::where('id', $in['id'])->first();
                $added_by = User::where('id',$scope->added_by)->first();
                $out['Added By'] = $added_by->name;
                return $out;
            }
        );
    }
}
