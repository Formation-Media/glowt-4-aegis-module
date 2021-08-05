@php
    $page_menu=array();
    $page_menu[]=array(
        'href' =>'/a/m/Aegis/projects/add/'.$scope->id,
        'icon' =>'file-plus',
        'title'=>__('Add Project to Scope')
    );
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'scopes' => __('scopes'),
                        $scope->name
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')
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

@endsection
