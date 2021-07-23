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
            '2021-07-23'=>array(
                'Glowt Core - 0.28.0'=>array(
                    'Now installable as app on Desktop and mobile',
                    'Changed icon set',
                    'UI Updates',
                    'Improved user deleting',
                    'Add support for APIs',
                    'Fix dashboard charts',
                    'Support wider range of chart types',
                    'Update iconography',
                    'Improvements to PDF renderer',
                    'Improvements to error processing',
                    'Developer improvements'
                ),
                'AEGIS - 0.9.3'=>array(
                    'Replace charts to use new functionality',
                    'Updated changelog'
                ),
                'HR - 0.19.0'=>array(
                    'Fixed issues with subjects not being correctly highlighted as incomplete',
                    'Added ability to edit active competency',
                    'Change Pen Profile to editor',
                    'UX fixes',
                    'Chart colour fixes',
                    'Highlight issues that need approving',
                    'Replace charts to use new functionality',
                    'Competency review fixes',
                    'Fix skill-less bugs',
                    '"The reporting figures at the top of the page is not reporting all missing items. If a 1,2 or 3, or justification is missing, this is counted as complete in the top figure as the first item is showing \'yes\'."',
                    '"When the form has been rejected, the reporting at the top is showing as complete, even though on my form there are four items that need to be amended."',
                    'Updated changelog'
                ),
            ),
            '2021-05-21'=>array(
                'Glowt Core - 0.22.2'=>'Fix for encoded characters in email field',
                'AEGIS - 0.9.1'=>'Updated changelog',
                'HR - 0.17.0'=>array(
                    'Add colour helper to incomplete subjects',
                    'Fix incomplete subject count'
                )
            ),
            '2021-05-18'=>array(
                'Glowt Core - 0.22.1'=>'Improved notification list',
                'AEGIS - 0.9.0'=>'Added changelog',
                'HR - 0.16.0'=>'Enable editing of competencies when denied.'
            ),
            '2021-05-14'=>array(
                'Glowt Core - 0.21.0'=>array(
                    'Improved server error page',
                    'Add versioning to help draw and management page',
                    'Miscellaneous bugfixes and improvements',
                    'Custom avatar support'
                ),
                'AEGIS - 0.8.4'=>'Support retrieval of version number',
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
                    @php
                        if(!is_array($changes)){
                            $changes=array($changes);
                        }
                    @endphp
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
