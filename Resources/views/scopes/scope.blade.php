@php
    $page_menu=array();
    $page_menu[]=array(
        'href' =>'/a/m/AEGIS/projects/add/'.$scope->id,
        'icon' =>'file-plus',
        'title'=>__('Add Project to Scope')
    );
    $tabs = [
        ['name' => __('dictionary.details')],
        ['name' => __('dictionary.projects')],
    ]
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>__('dictionary.management'),
            $module->getName(),
            $module_base.'scopes' => __('Scopes'),
            $scope->name
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')
    <x-tabs name="scope" :tabs="$tabs">
        <x-tab target="{{__('Details')}}">
            <x-card :details="$scope_details"/>
            <x-form name="scope">
                <x-card>
                    <x-field
                        name="name"
                        label="{{__('Name')}}"
                        type="text"
                        value="{{ $scope->name}}"
                        required
                    />
                </x-card>
                <x-field type="actions">
                    <x-slot name="center">
                        <x-field label="{{ __('Update') }}" name="add" type="submit" style="primary"/>
                    </x-slot>
                </x-field>
            </x-form>
        </x-tab>
        <x-tab target="{{__('Projects')}}">
            <x-table selects controller="Projects" module="AEGIS" method="view" type="classic" id="{{$scope->id}}" />
        </x-tab>
    </x-tabs>
@endsection
