@extends(
    'layouts.account',
    array(
        'breadcrumbs'=> array(
            'management'=>'dictionary.management',
            $module->getName(),
            'aegis::phrases.project-management'
        ),
    )
)
@section('content')
    <div class="grid-sm-2 grid-lg-3 grid-xl-4">
        @foreach($columns as $name => $issues)
            <div>
                <h2>{{ $name }} <small class="text-muted">({{ number_format(count($issues)) }})</small></h2>
                @foreach(array_filter($issues) as $number => $issue)
                    <x-card body-class="p-3">
                        <h3 class="h4">
                            @if(strpos($auth_user->email, '@formationmedia.co.uk') !== false)
                                <a href="{{ $issue['link'] }}" target="_blank">
                            @endif
                            #{{ $number }} - {{ $issue['title'] }}
                            @if(strpos($auth_user->email, '@formationmedia.co.uk') !== false)
                                </a>
                            @endif
                        </h3>
                        <div class="mt-2">Added: {{ $issue['date'] }}</div>
                        <div class="mt-2">
                            @if ($issue['labels'])
                                @foreach($issue['labels'] as $label => $color)
                                    <span class="badge text-white" style="background-color:#{{ $color }}">{{ $label }}</span>
                                @endforeach
                            @endif
                        </div>
                        <div class="mt-2">
                            @if($issue['body'] && !str_contains($issue['body'], '## Trace'))
                                {!! Str::markdown($issue['body']) !!}
                            @endif
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endforeach
    </div>
@endsection
