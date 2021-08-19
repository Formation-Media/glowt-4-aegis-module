<?php

namespace Modules\AEGIS\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\Toast;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\ProjectVariant;
use Modules\AEGIS\Models\VariantDocument;

class ProjectsController extends Controller
{
    public function delete_project($request){
        foreach($request->ids as $id){
            $project = Project::find($id);
            $project_variants = ProjectVariant::where('project_id', $project->id)->get();
            foreach($project_variants as $variant){
                $variant->delete();
            }
            $project->delete();
        }
    }

    public function get_project_variants($request){
        $variants=[];
        if(isset($request->project)){
            $project = Project::find($request->project);
            $variants = $project->variants->pluck('name', 'id')->toArray();
        }
        return compact('variants');
    }

    public function table_view($request){
        $actions=array(
            array(
                'style'=>'primary',
                'name' =>'View',
                'uri'  =>'/a/m/AEGIS/projects/project/{{id}}'
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
                    'default_sort'=>'asc',
                    'sortable'    =>true,
                ),
                'Type'=>array(
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
            function ($query) use($request){

                if($request->id){
                    return $query->where('scope_id', $request->id);
                }
                return $query;
            },
            function ($in,$out){
                $project = Project::where('id', $in['id'])->first();
                $added_by = User::where('id',$project->added_by)->first();
                $out['Added By'] = $added_by->name;
                $out['Name'] = $project->id.': '.$project->name;
                $out['Type'] = $project->type->name;
                return $out;
            }
        );
    }

    public function table_variantdocumentsview($request){
        $actions=array(
            array(
                'style'=>'primary',
                'name' =>'View',
                'uri'  =>'/a/m/Documents/document/document/{{document_id}}'
            ),
        );
        $row_structure=array(
            'actions'=>$actions,
            'data'=>array(
                'ID'=>array(
                    'columns'=>'id',
                    'display'=>false
                ),
                'DOCUMENT_ID'=>array(
                    'columns'=>'document_id',
                    'display'=> false
                ),
                'Name'=>array(
                    'default_sort'=>'asc',
                    'sortable'    =>true,
                ),
                'Status'=>array(
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
        return parent::to_ajax_table('VariantDocument',$row_structure,[],
            function ($query) use($request){
                return $query->where('variant_id', $request->id);
            },
            function ($in,$out){
                $variant_document = VariantDocument::where('id', $in['id'])->first();
                $added_by = User::where('id', $variant_document->document->added_by)->first();
                $out['Name'] =  $variant_document->document->name;
                $out['Status'] = $variant_document->document->status;
                $out['Added By'] = $added_by->name;
                return $out;
            }
        );
    }
}
