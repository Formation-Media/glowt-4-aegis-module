@php
    $page_menu=array();
    $page_menu[]=array(
        'href' =>'/a/m/Aegis/projects/add-variant',
        'icon' =>'file-plus',
        'title'=>__('Add Variant')
    );
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'projects' => __('Projects'),
                        $project->name
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')

@endsection
