@php
    $page_menu=array();
    $page_menu[]=array(
        'href' =>'/a/m/AEGIS/scopes/add',
        'icon' =>'file-plus',
        'title'=>__('Add Scope')
    );
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            $module->getName(),
            __('Scopes')
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')
    <x-table selects  api="scopes" method="view" type="classic" module="AEGIS"/>
@endsection