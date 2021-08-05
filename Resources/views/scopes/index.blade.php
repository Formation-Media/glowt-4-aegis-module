@php
    $page_menu=array();
    $page_menu[]=array(
        'href' =>'/a/m/Aegis/scopes/add',
        'icon' =>'file-plus',
        'title'=>__('Add Scope')
    );
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            __('Scopes')
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')
    <x-table selects  api="scopes" method="view" type="new" module="Aegis"/>
@endsection
