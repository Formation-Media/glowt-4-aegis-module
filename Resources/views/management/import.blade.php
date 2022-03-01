@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>___('dictionary.management'),
            $module->getName(),
            ___('dictionary.import')
        ),
    )
)
@section('content')
    <x-progress :percentage="0"/>
    <x-card>
        <ol class="mb-0 js-messages" reversed>
            <li>Starting&hellip;</li>
        </ol>
    </x-card>
@endsection
