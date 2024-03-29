@php
    $page_menu=array();
    if($permissions['add']){
        $page_menu[]=array(
            'href'  => 'companies/add',
            'icon'  => 'plus',
            'title' => 'Add AEGIS Company'
        );
    }
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>___('dictionary.management'),
            $module->getName(),
            ___('dictionary.companies')
        ),
        'page_menu'=>$page_menu,
    )
)

@section('content')
    <x-table selects controller="companies" method="companies" module="AEGIS" />
@endsection
