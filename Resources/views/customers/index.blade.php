@php
    $page_menu=array();
    $page_menu[]=array(
        'href' =>'/a/m/AEGIS/customers/add',
        'icon' =>'file-plus',
        'title'=>___('Add Customer')
    );
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>'dictionary.management',
            $module->getName(),
            'dictionary.customers'
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')
    <x-table selects controller="customers" method="view" type="classic" module="AEGIS"/>
    @include(
        'aegis::_partials.modals.merge',
        compact(
            'customers'
        )
    )
@endsection
