@php
    $page_menu=array();
    $page_menu[]=array(
        'href' =>'/a/m/AEGIS/projects/add',
        'icon' =>'file-plus',
        'title'=>___('Add Project')
    );
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            $module->getName(),
            ___('Projects')
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')
    <x-table selects controller="Projects" method="view" type="classic" module="AEGIS"/>
@endsection
