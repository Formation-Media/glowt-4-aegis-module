<?php

namespace Modules\AEGIS\Http\Controllers;

use Modules\AEGIS\Models\CompetencyCompany;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\JobTitle;
use Modules\AEGIS\Models\Project;
use Modules\AEGIS\Models\ProjectVariant;
use Modules\AEGIS\Models\UserGrade;
use Modules\AEGIS\Models\VariantDocument;
use Modules\Documents\Models\Category;

class HooksController extends AEGISController
{
    public static function chart_competencies_by_company($settings){
        return array(
            'method'=>'competencies_by_company',
            'module'=>'AEGIS',
            'title' =>__('Competencies by Company'),
            'type'  =>'donut'
        );
    }
    public static function collect_add_user($args){
        $user =$args['user'];
        $aegis=$args['request']->aegis;
        $user->setMeta([
            'aegis.discipline'=>$aegis['discipline'],
            'aegis.grade'     =>$aegis['grade']??null,
            'aegis.type'      =>$aegis['type']
        ]);
        $user->save();
    }

    public static function collect_documents__view_add_document_fields($args){
        $reference = VariantDocument::where('document_id', $args['document'])->first()->reference;
        $projects = Project::all()->pluck('name', 'id')->toArray();
        $project_variants = null;
        $selected_variant = null;
        $selected_project = null;
        if(isset($_GET['project_variant'])){
            $selected_variant = ProjectVariant::find($_GET['project_variant']);
            $selected_project = $selected_variant->project;
            $project_variants = $selected_project->variants->pluck('name', 'id')->toArray();
        }
        return view(
            'aegis::_hooks.add-document-fields',
            compact(

                'projects',
                'project_variants',
                'reference',
                'selected_project',
                'selected_variant'
            )
        );
    }
    public static function collect_documents__view_document_fields($document){
        $projects = Project::all()->pluck('name','id')->toArray();
        $document_variant = VariantDocument::where('document_id', $document->id)->first();
        if($document_variant){
            $selected_variant = $document_variant->project_variant;
            $selected_project = $document_variant->project_variant->project;
            $project_variants = $selected_project->variants->pluck('name','id')->toArray();
        } else {
            $selected_variant = null;
            $selected_project = null;
            $project_variants = [];
        }
        return view(
            'aegis::_hooks.add-document-fields',
            compact(
                'projects',
                'project_variants',
                'selected_project',
                'selected_variant'
            )
        );
    }
    public static function collect_documents__add_document($args){
        if( isset($args['request']->aegis['project_variant'])){
            $category = Category::find($args['request']->category);
            $count = VariantDocument
                ::where('variant_id', $args['request']->aegis['project_variant'] )
                ->where('category_id', $category->id)
                ->count();
            $variant_document = new VariantDocument();
            $variant_document->document_id = $args['new_document']->id;
            $variant_document->variant_id = $args['request']->aegis['project_variant'];
            $project_variant = ProjectVariant::find($args['request']->aegis['project_variant']);
            $variant_document->reference = $project_variant->reference.'/'.$category->prefix.$count;
            $variant_document->save();
        }
    }
    public static function collect_documents__edit_document($args){
        if(isset($args['request']->aegis['project_variant'])){
            $variant_document = VariantDocument::updateOrCreate(
                ['document_id' => $args['document']->id ],
                ['variant_id' => $args['request']->aegis['project_variant']]
            );
        }
    }
    // public static function collect_documents__view_document($args){
    //     if(isset($args['document'])){
    //         VariantDocument::where('document_id', )
    //     }

    // }
    public static function collect_hr__add_competency($args){
        if(isset($args['request']->aegis)){
            $competency_company               =new CompetencyCompany;
            $competency_company->competency_id=$args['competency']->id;
            $competency_company->company_id   =$args['request']   ->aegis['company'];
            $competency_company->save();
        }
    }
    public static function collect_hr__edit_competency($args){
        if(isset($args['request']->aegis)){
            if($cc=CompetencyCompany::where('competency_id',$args['competency']->id)->first()){
                $cc->update([
                    'company_id'=>$args['request']->aegis['company']
                ]);
            }else{
                $competency_company               =new CompetencyCompany;
                $competency_company->competency_id=$args['competency']->id;
                $competency_company->company_id   =$args['request']   ->aegis['company'];
                $competency_company->save();
            }
        }
    }
    public static function collect_hr__view_add_competency_fields($args){
        $company_data=Company::all();
        $companies   =array();
        if(sizeof($company_data)){
            foreach($company_data as $company){
                $companies[$company->id]=$company->name;
            }
        }
        return view(
            'aegis::_hooks.add-competency-fields',
            compact(
                'companies'
            )
        );
    }
    public static function collect_hr__view_competency_fields($competency){
        $company_data=Company::all();
        $companies   =array();
        $value       =CompetencyCompany::where('competency_id',$competency->id)->first();
        if($value){
            $value=$value->company_id;
        }
        if(sizeof($company_data)){
            foreach($company_data as $company){
                $companies[$company->id]=$company->name;
            }
        }
        if($bio=$competency->user->getMeta('hr.bio')??null){
            $bio=nl2br($bio);
        }
        return view(
            'aegis::_hooks.add-competency-fields',
            compact(
                'bio',
                'competency',
                'companies',
                'value'
            )
        );
    }
    public static function collect_hr__view_competency_summary($competency,$module){
        if($bio=$competency->user->getMeta('hr.bio')??null){
            $bio=nl2br($bio);
        }
        return view(
            'aegis::_hooks.hr.competency-summary',
            compact(
                'bio'
            )
        );
    }
    public static function collect_hr__view_set_up($args){
        $permissions=\Auth::user()->feature_permissions('AEGIS','companies');
        return view('aegis::_hooks.set-up-page',compact('permissions'));
    }
    public static function collect_store_user($args){
		$user=$args['user'];
        $user->setMeta([
            'aegis.discipline'=>$args['request']->aegis['discipline'],
            'aegis.grade'     =>$args['request']->aegis['grade']??null,
            'aegis.type'      =>$args['request']->aegis['type']
        ]);
        $user->save();
    }
    public static function collect_view_add_user($data,$module){
        return self::_add_user_hook('add');
    }
    public static function collect_view_edit_profile($data,$module){
        return self::_add_user_hook('profile',$data);
    }
    public static function collect_view_edit_user($data,$module){
        return self::_add_user_hook('view',$data);
    }
    public static function collect_dashboard_charts($data,$module){
        return array(
            'Competencies by Company'=>array(
                'method'=>'chart_competencies_by_company'
            )
        );
    }
    public static function collect_view_management($args){
        return array(
            '/a/m/AEGIS/scopes'  =>__('Scopes'),
            '/a/m/AEGIS/management/changelog'  =>__('Changelog'),
            '/a/m/AEGIS/management/job-titles' =>__('Job Titles'),
            '/a/m/AEGIS/management/user-grades'=>__('User Grades'),
            '/a/m/AEGIS/management/types'      =>__('Project Types')
        );
    }
    public static function collect_view_table_filter($args){
        $companies=array();
        if($competency_companies=Company::all()){
            foreach($competency_companies as $company){
                $companies[$company->id]=$company->name;
            }
        }
        return view(
            'aegis::_hooks.table-filter',
            compact(
                'args',
                'companies'
            )
        )->render();
    }
    public static function filter_hr__ajax_table_competencies($args){
        $request=$args['request'];
        if($request->filter){
            if(isset($request->filter['company'])){
                if($ids=CompetencyCompany::select('competency_id')->where('company_id',$request->filter['company'])->get()){
                    $ids=array_column($ids->toArray(),'competency_id');
                    $args['query']->whereIn('m_hr_competencies.id',$ids);
                }
            }
        }
        return $args;
    }

    public static function filter_main_menu(&$data,$module){
        $data[]=array(
            'icon'    =>'folder',
            'link'    =>'/a/m/'.$module->getName().'/projects',
            'title'   =>__('Projects'),
        );
        $data[]=array(
            'icon' => 'object-group',
            'link'    =>'/a/m/'.$module->getName().'/scopes',
            'title'   =>__('Scopes'),
        );
    }

    private static function _add_user_hook($method,$user=null){
        return view(
            'aegis::_hooks.add-user',
            array(
                'grades'    =>UserGrade::formatted(),
                'job_titles'=>JobTitle::formatted(),
                'method'    =>$method,
                'types'     =>self::user_types(),
                'user'      =>$user
            )
        )->render();
    }
}
