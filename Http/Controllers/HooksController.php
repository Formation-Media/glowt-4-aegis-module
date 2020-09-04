<?php

namespace Modules\Aegis\Http\Controllers;

use Modules\Aegis\Models\CompetencySupplier;
use Modules\Aegis\Models\Supplier;

class HooksController extends AegisController
{
    const dashboard_charts=array(
        'Competencies by Supplier'=>array(
            'method'=>'chart_competencies_by_supplier'
        )
    );
    public static function chart_competencies_by_supplier($settings){
        $data=array();
        $suppliers=CompetencySupplier::select([
                                        \DB::raw('count(m_aegis_suppliers.status) as count,m_aegis_suppliers.name')
                                     ])
                                     ->join('m_aegis_suppliers','m_aegis_competency_supplier.supplier_id','=','m_aegis_suppliers.id')
                                     ->groupBy('name')
                                     ->orderBy('status')
                                     ->get();
        if($suppliers){
            foreach($suppliers as $supplier){
                $data[]=array(
                    'count'=>$supplier->count,
                    'name' =>$supplier->name
                );
            }
        }
        return array(
            'data' =>$data,
            'title'=>__('Competencies by Supplier'),
            'type' =>'donut'
        );
    }
    public static function collect_view_add_competency_fields($args){
        $supplier_data=Supplier::all();
        $suppliers    =array();
        if(sizeof($supplier_data)){
            foreach($supplier_data as $supplier){
                $suppliers[$supplier->id]=$supplier->name;
            }
        }
        return view(
            'aegis::hooks.add-competency-fields',
            compact(
                'suppliers'
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
    public static function collect_view_competency_fields($args){
        $supplier_data=Supplier::all();
        $suppliers    =array();
        $value        =CompetencySupplier::where('competency_id',$args->id)->first();
        if($value){
            $value=$value->supplier_id;
        }
        if(sizeof($supplier_data)){
            foreach($supplier_data as $supplier){
                $suppliers[$supplier->id]=$supplier->name;
            }
        }
        return view(
            'aegis::hooks.add-competency-fields',
            compact(
                'suppliers',
                'value'
            )
        );
    }
    public static function collect_dashboard_charts($data,$module){
        return self::dashboard_charts;
    }
    public static function collect_hr__add_competency($args){
        $competency_supplier               =new CompetencySupplier;
        $competency_supplier->competency_id=$args['competency']->id;
        $competency_supplier->supplier_id  =$args['request']   ->aegis['supplier'];
        $competency_supplier->save();
    }
    public static function collect_view_set_up($args){
        return view('aegis::hooks.set-up-page');
    }
}
