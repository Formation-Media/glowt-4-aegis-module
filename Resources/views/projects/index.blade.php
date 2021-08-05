@php
    $page_menu=array();
    $page_menu[]=array(
        'href' =>'/a/m/Aegis/projects/add',
        'icon' =>'file-plus',
        'title'=>__('Add Project')
    );
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            __('Projects')
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')
    <x-table selects  api="Projects" method="view" type="new" module="Aegis"/>
@endsection
