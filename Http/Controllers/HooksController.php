<?php

namespace Modules\AEGIS\Http\Controllers;

use Modules\AEGIS\Models\CompetencyCompany;
use Modules\AEGIS\Models\Company;
use Modules\AEGIS\Models\JobTitle;
use Modules\AEGIS\Models\UserGrade;

class HooksController extends AEGISController
{
    public static function chart_competencies_by_company($settings){
        $data     =array();
        $companies=CompetencyCompany
            ::select([
                \DB::raw('count(m_aegis_companies.status) as count,m_aegis_companies.name')
            ])
            ->join('m_aegis_companies','m_aegis_competency_company.company_id','=','m_aegis_companies.id')
            ->groupBy('name')
            ->orderBy('status')
            ->get();
        if($companies){
            foreach($companies as $company){
                $data[]=array(
                    'count'=>$company->count,
                    'name' =>$company->name
                );
            }
        }
        return array(
            'data' =>$data,
            'title'=>__('Competencies by Company'),
            'type' =>'donut'
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
    public static function collect_hr__add_competency($args){
        if(isset($args['request']->aegis)){
            $competency_company               =new CompetencyCompany;
            $competency_company->competency_id=$args['competency']->id;
            $competency_company->company_id   =$args['request']   ->aegis['company'];
            $competency_company->save();
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
            '/a/m/AEGIS/management/job-titles' =>'Job Titles',
            '/a/m/AEGIS/management/user-grades'=>'User Grades'
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
