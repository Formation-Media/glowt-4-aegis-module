@php
    $page_menu=array(
        array(
            'class'=> 'js-add-type',
            'icon' =>'plus',
            'title'=>___('Add Type')
        )
    );
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>___('dictionary.management'),
            $module->getName(),
            ___('Types')
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')
    <x-table selects controller="management" method="types" type="classic" module="AEGIS"/>
    <x-modal id="add-type" title="{{___('Add Type')}}" save-style="success" save-text="Add">
        <x-form name="types">
            <x-field
                label="{{ ___('Name') }}"
                name="name"
                required
                type="text"
            />
        </x-form>
    </x-modal>
@endsection
