
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'aegis::dictionary.training',
        ),
    )
)
@section('content')
    <x-card-view
        model="Training"
        module="AEGIS"
        view="row"
    />
@endsection
