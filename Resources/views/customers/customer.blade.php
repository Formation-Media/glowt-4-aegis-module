@php
    $page_menu=array();
    $page_menu[]=array(
        'href' =>'/a/m/AEGIS/projects/add/'.$customer->id,
        'icon' =>'file-plus',
        'title'=>___('Add Project to Customer')
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
            $module_base.'customers' => ___('Customers'),
            $customer->name
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')
    <x-tabs name="customer" :tabs="$tabs">
        <x-tab target="{{___('Details')}}">
            <x-card :details="$customer_details"/>
            <x-form name="customer">
                <x-card>
                    <x-field
                        name="name"
                        label="{{___('Name')}}"
                        type="text"
                        value="{{ $customer->name}}"
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
            <x-table selects controller="Projects" module="AEGIS" method="view" type="classic" id="{{$customer->id}}" />
        </x-tab>
    </x-tabs>
@endsection
