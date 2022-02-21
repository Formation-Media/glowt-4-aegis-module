@php
    $page_menu=array();
    $page_menu[]=array(
        'href' =>'/a/m/AEGIS/scopes/add',
        'icon' =>'file-plus',
        'title'=>___('Add Scope')
    );
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>___('dictionary.management'),
            $module->getName(),
            ___('Scopes')
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')
    <x-table selects controller="scopes" method="view" type="classic" module="AEGIS"/>
@endsection
