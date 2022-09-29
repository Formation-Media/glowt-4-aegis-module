@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>'dictionary.management',
            $module->getName(),
            $module_base.'management/import' => 'dictionary.import',
            'aegis::phrases.import-testing',
        ),
    )
)
@section('content')
    <x-tabs name="tabs" :tabs="$tabs">
        @if ($errors)
            <x-tab target="errors">
                <x-list-group>
                    @foreach($errors as $group => $items)
                        <x-list-group-item title="{{ $group }}">
                            @php
                                ksort($items);
                            @endphp
                            <ol class="cols-md-2">
                                @foreach ($items as $document => $error)
                                    <li><strong>{{ $document }}</strong> {{ $error }}</li>
                                @endforeach
                            </ol>
                        </x-list-group-item>
                    @endforeach
                </x-list-group>
            </x-tab>
        @endif
        @if ($projects)
            <x-tab target="data">
                <x-list-group>
                    @foreach(array_slice($projects, 0, 25) as $project_reference => $project)
                        <x-list-group-item>
                            <h2>Project: {{ $project_reference }} - {{ $project['name'] }}</h2>
                            <div class="grid-6 mb-0">
                                <x-details
                                    :details="[
                                        'company'     => $project['company'],
                                        'description' => $project['description'],
                                        'customer'    => $project['customer'],
                                        'type'        => $project['type'],
                                    ]"
                                    cols-lg="1"
                                    cols-xxl="1"
                                />
                                @if ($project['phases'])
                                    <div class="span-5">
                                        <x-list-group>
                                            @foreach($project['phases'] as $phase_number => $phase)
                                                <x-list-group-item>
                                                    <h3>Phase: {{ $phase_number }} - {{ $phase['name'] }}</h3>
                                                    <div class="grid-5 mb-0">
                                                        <x-details
                                                            :details="[
                                                                'reference'   => $phase['reference'],
                                                                'description' => $phase['description'],
                                                            ]"
                                                            cols-lg="1"
                                                            cols-xxl="1"
                                                        />
                                                        @if ($phase['documents'])
                                                            <div class="span-4">
                                                                <x-list-group>
                                                                    @foreach($phase['documents'] as $document_reference => $document)
                                                                        <x-list-group-item>
                                                                            <h4>Document: {{ $document_reference }} - {{ $document['name'] }}</h4>
                                                                            <div class="grid-4 mb-0">
                                                                                <x-details
                                                                                    :details="[
                                                                                        'category'   => $document['category'].' ('.$document['category_prefix'].')',
                                                                                        'created_at' => $document['created_at'],
                                                                                        'created_by' => $document['created_by'],
                                                                                        'issue'      => $document['issue'],
                                                                                        'status'     => $document['status'] ?? null,
                                                                                    ]"
                                                                                    cols-lg="1"
                                                                                    cols-xxl="1"
                                                                                />
                                                                                <div class="span-3">
                                                                                    @php
                                                                                        $reference = \Str::slug($document_reference);
                                                                                        $tabs      = [];
                                                                                        if ($document['approval']) {
                                                                                            $tabs[$reference.'approval'] = 'Approval';
                                                                                        }
                                                                                        if ($document['comments']) {
                                                                                            $tabs[$reference.'comments'] = 'Comments';
                                                                                        }
                                                                                        if ($document['feedback_list']) {
                                                                                            $tabs[$reference.'feedback'] = 'Feedback List';
                                                                                        }
                                                                                    @endphp
                                                                                    @if ($tabs)
                                                                                        <x-tabs name="tabs" :tabs="$tabs">
                                                                                            @if ($document['approval'])
                                                                                                <x-tab target="{{$reference}}approval">
                                                                                                    @foreach ($document['approval'] as $role => $issues)
                                                                                                        <h5>{{ ucwords($role) }} Role</h5>
                                                                                                        <x-list-group>
                                                                                                            @foreach($issues as $issue_number => $items)
                                                                                                                <x-list-group-item>
                                                                                                                    <h6>Issue: {{ $issue_number }}</h6>
                                                                                                                    <x-list-group>
                                                                                                                        @php
                                                                                                                            $stage = 0;
                                                                                                                        @endphp
                                                                                                                        @foreach($items as $users)
                                                                                                                            <x-list-group-item>
                                                                                                                                <h6>Stage: {{ ++$stage }}</h6>
                                                                                                                                <x-list-group>
                                                                                                                                    @foreach($users as $user_reference => $user)
                                                                                                                                        <x-list-group-item>
                                                                                                                                            <h6>{{ ucwords($role) }}: {{ $user_reference }}</h6>
                                                                                                                                            @dump($user)
                                                                                                                                        </x-list-group-item>
                                                                                                                                    @endforeach
                                                                                                                                </x-list-group>
                                                                                                                            </x-list-group-item>
                                                                                                                        @endforeach
                                                                                                                    </x-list-group>
                                                                                                                </x-list-group-item>
                                                                                                            @endforeach
                                                                                                        </x-list-group>
                                                                                                    @endforeach
                                                                                                </x-tab>
                                                                                            @endif
                                                                                            @if ($document['comments'])
                                                                                                <x-tab target="{{$reference}}comments">
                                                                                                    <x-list-group>
                                                                                                        @foreach($document['comments'] as $comment)
                                                                                                            <x-list-group-item>
                                                                                                                @dump($comment)
                                                                                                            </x-list-group-item>
                                                                                                        @endforeach
                                                                                                    </x-list-group>
                                                                                                </x-tab>
                                                                                                @endif
                                                                                            @if ($document['feedback_list'])
                                                                                                <x-tab target="{{$reference}}feedback">
                                                                                                    @dump([
                                                                                                        'final' => $document['feedback_list']['final'] ? 'Yes' : 'No',
                                                                                                        'type'  => $document['feedback_list']['type'],
                                                                                                        'name'  => $document['feedback_list']['name'],
                                                                                                    ])
                                                                                                </x-tab>
                                                                                            @endif
                                                                                        </x-tabs>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </x-list-group-item>
                                                                    @endforeach
                                                                </x-list-group>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </x-list-group-item>
                                            @endforeach
                                        </x-list-group>
                                    </div>
                                @endif
                            </div>
                        </x-list-group-item>
                    @endforeach
                </x-list-group>
            </x-tab>
        @endif
    </x-tabs>
@endsection
@section('styles')
    <style>
        pre,
        .list-group,
        .tab-content {
            margin-bottom:0;
        }
    </style>
@endsection
