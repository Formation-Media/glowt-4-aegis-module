@php
    $page_menu=array();
    $page_menu[]=array(
        'href'  => '/a/m/AEGIS/projects/add',
        'icon'  => 'file-plus',
        'title' => ['phrases.add', ['item' => ___('dictionary.project')]],
    );
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            $module->getName(),
            'dictionary.projects'
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')
    <x-table selects controller="Projects" method="view" type="classic" module="AEGIS"/>
@endsection
