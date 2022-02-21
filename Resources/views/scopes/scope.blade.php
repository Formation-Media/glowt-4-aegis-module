@php
    $page_menu=array();
    $page_menu[]=array(
        'href' =>'/a/m/AEGIS/projects/add/'.$scope->id,
        'icon' =>'file-plus',
        'title'=>___('Add Project to Scope')
    );
    $tabs = [
        ['name' => ___('dictionary.details')],
        ['name' => ___('dictionary.projects')],
    ]
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>___('dictionary.management'),
            $module->getName(),
            $module_base.'scopes' => ___('Scopes'),
            $scope->name
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')
    <x-tabs name="scope" :tabs="$tabs">
        <x-tab target="{{___('Details')}}">
            <x-card :details="$scope_details"/>
            <x-form name="scope">
                <x-card>
                    <x-field
                        name="name"
                        label="{{___('Name')}}"
                        type="text"
                        value="{{ $scope->name}}"
                        required
                    />
                </x-card>
                <x-field type="actions">
                    <x-slot name="center">
                        <x-field label="{{ ___('Update') }}" name="add" type="submit" style="primary"/>
                    </x-slot>
                </x-field>
            </x-form>
        </x-tab>
        <x-tab target="{{___('Projects')}}">
            <x-table selects controller="Projects" module="AEGIS" method="view" type="classic" id="{{$scope->id}}" />
        </x-tab>
    </x-tabs>
@endsection
