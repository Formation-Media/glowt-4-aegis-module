@php
    $page_menu=array();
    if($permissions['add']){
        $page_menu[]=array(
            'href'  => 'companies/add',
            'icon'  => 'plus',
            'title' => 'Add Competency Company'
        );
    }
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            str_replace($module->getName(),'HR',$module_base)                       => 'HR',
            str_replace($module->getName(),'HR',$module_base).'competencies'        => __('dictionary.competencies'),
            str_replace($module->getName(),'HR',$module_base).'competencies/set-up' => __('Set-up'),
            __('dictionary.companies')
        ),
        'page_menu'=>$page_menu,
    )
)

@section('content')
    <x-table selects controller="companies" method="companies" module="AEGIS" />
@endsection
