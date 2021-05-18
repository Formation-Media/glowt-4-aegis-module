@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>'Management',
            __('Changelog')
        )
    )
)
@section('content')
    @php
        $changes=array(
            '2021-05-18'=>array(
                'Glowt Core - 0.21.1'=>array(
                    'Improved notification list'
                ),
                'AEGIS - 0.9.0'=>array(
                    'Added changelog'
                ),
                'HR - 0.16.0'=>array(
                    'Enable editing of competencies when denied.'
                )
            ),
            '2021-05-14'=>array(
                'Glowt Core - 0.21.0'=>array(
                    'Improved server error page',
                    'Add versioning to help draw and management page',
                    'Miscellaneous bugfixes and improvements',
                    'Custom avatar support'
                ),
                'AEGIS - 0.8.4'=>array(
                    'Support retrieval of version number'
                ),
                'HR - 0.15.4'=>array(
                    'Support retrieval of version number',
                    'Fix edit button on Competencies'
                )
            )
        );
        $changes=array_slice($changes,0,\Config::get('settings.lists.items-per-page'));
        $details=array(
            'Last Updated'=>\App\Helpers\Dates::datetime(filemtime(__FILE__))
        );
    @endphp
    <x-card :details="$details"/>
    @foreach ($changes as $date=>$modules)
        <h2>{{ \App\Helpers\Dates::date($date) }}</h2>
        <x-list-group class="shadow">
            @foreach ($modules as $module=>$changes)
                <x-list-group-item title="{{ $module }}">
                    <ol reversed class="cols-md-2 mb-0">
                        @foreach ($changes as $change)
                            <li>{!! $change !!}</li>
                        @endforeach
                    </ol>
                </x-list-group-item>
            @endforeach
        </x-list-group>
    @endforeach
@endsection
