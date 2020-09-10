<?php

namespace Modules\AEGIS\Http\Controllers;

use Modules\AEGIS\Models\CompetencyCompany;
use Modules\AEGIS\Models\Company;

class HooksController extends AEGISController
{
    const dashboard_charts=array(
        'Competencies by Company'=>array(
            'method'=>'chart_competencies_by_company'
        )
    );
    public static function chart_competencies_by_company($settings){
        $data=array();
        $companies=CompetencyCompany::select([
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
    public static function collect_view_add_competency_fields($args){
        $company_data=Company::all();
        $companies   =array();
        if(sizeof($company_data)){
            foreach($company_data as $company){
                $companies[$company->id]=$company->name;
            }
        }
        return view(
            'aegis::hooks.add-competency-fields',
            compact(
                'companies'
            )
        );
    }
    public static function collect_add_user($args){
        $user=$args['user'];
        $user->setMeta([
            'aegis.discipline'=>$args['request']->aegis['discipline'],
            'aegis.grade'     =>$args['request']->aegis['grade'],
            'aegis.type'      =>$args['request']->aegis['type']
        ]);
        $user->save();
    }
    public static function collect_view_add_user($data,$module){
        return view(
            'aegis::hooks.add-user',
            array(
                'types'=>self::user_types()
            )
        )->render();
    }
    public static function collect_view_competency_fields($competency){
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
        return view(
            'aegis::hooks.add-competency-fields',
            compact(
                'competency',
                'companies',
                'value'
            )
        );
    }
    public static function collect_dashboard_charts($data,$module){
        return self::dashboard_charts;
    }
    public static function collect_hr__add_competency($args){
        $competency_company               =new CompetencyCompany;
        $competency_company->competency_id=$args['competency']->id;
        $competency_company->company_id   =$args['request']   ->aegis['company'];
        $competency_company->save();
    }
    public static function collect_view_set_up($args){
        return view('aegis::hooks.set-up-page');
    }
    public static function collect_view_table_filter($args){
        $companies=array();
        if($competency_companies=Company::all()){
            foreach($competency_companies as $company){
                $companies[$company->id]=$company->name;
            }
        }
        return view(
            'aegis::hooks.table-filter',
            compact(
                'args',
                'companies'
            )
        )->render();
    }
    // public static function filter_ajax_table_competencies($args){
    //     $args['query'];
    //     $args['request'];
    //     $args['query']=$args['query']->where();
    //     \Log::debug([
    //         __FILE__=>__LINE__,
    //         $request->all()
    //     ]);
    // }
}
