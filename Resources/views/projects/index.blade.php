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
            'm/Documents/document' => 'dictionary.documents',
            'dictionary.projects'
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')
    <x-card-view
        model="Project"
        module="AEGIS"
        view="row"
    />
@endsection
