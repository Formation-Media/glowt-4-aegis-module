
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'm/Documents/document' => 'dictionary.documents',
            'dictionary.projects'
        ),
        'page_menu'=> array(
            array(
                'href'  => '/a/m/AEGIS/projects/add',
                'icon'  => 'file-plus',
                'title' => ['phrases.add', ['item' => ___('dictionary.project')]],
            )
        )
    )
)
@section('content')
    <x-card-view
        model="Project"
        module="AEGIS"
        view="row"
    />
@endsection
