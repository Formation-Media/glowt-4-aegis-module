
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'Aegis',
            'projects' => __('Projects'),
            'project/'.$project->id => $project->name,
            __('Add Variant')
        ),

    )
)
@section('content')

@endsection
