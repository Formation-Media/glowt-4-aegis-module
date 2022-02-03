@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>'dictionary.management',
            $module->getName(),
            $module_base.'management/import' => 'dictionary.import',
            'aegis::phrases.import-errors',
        ),
    )
)
@section('content')
    <x-card>
        <ol>
            @foreach($users as $user)
                <li>{{ $user->name }} ({{ $user->email }}) = </li>
            @endforeach
        </ol>
    </x-card>
    <x-card>
        @if($sections)
            <ul>
                @foreach ($sections as $section => $errors)
                    <li>{{ $section }}
                        <ol class="ps-5">
                            @foreach ($errors as $reference => $message)
                                <li>
                                    @if(!is_numeric($reference))
                                        <strong>{{ $reference }}</strong>
                                    @endif
                                    {{ $message }}
                                </li>
                            @endforeach
                        </ol>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-card>
@endsection
