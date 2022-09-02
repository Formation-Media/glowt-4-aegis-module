
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'm/Documents/document' => 'dictionary.documents',
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
