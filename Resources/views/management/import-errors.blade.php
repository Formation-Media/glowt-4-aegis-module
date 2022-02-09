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
    <x-table type="manual">
        <x-slot name="thead">
            <tr>
                <th>#</th>
                <th>Section</th>
                <th>Company</th>
                <th>Project</th>
                <th>Document Reference</th>
                <th>Full Reference</th>
                <th>Reason</th>
            </tr>
        </x-slot>
        @if($sections)
            @php
                $i = 0;
            @endphp
            @foreach ($sections as $section => $companies)
                @foreach($companies as $company => $projects)
                    @foreach($projects as $project => $references)
                        @foreach($references as $message => $reference)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>{{ $section }}</td>
                                <td>{{ $company }}</td>
                                <td>{{ $project }}</td>
                                <td>{{ $reference }}</td>
                                <td>{{ $company === 'Unknown' ? $reference : $company.'/'.$project.'/'.$reference }}</td>
                                <td>{{ $message }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
        @endif
    </x-table>
@endsection
