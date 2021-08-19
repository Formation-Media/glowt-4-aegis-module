@php
    $page_menu=array(
        array(
                'class'=> 'js-add-type',
                'icon' =>'plus',
                'title'=>__('Add Type')
            )
    );
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>'Management',
            $module->getName(),
            __('Types')
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')
    <x-table selects  api="management" method="types" type="classic" module="AEGIS"/>
    <x-modal id="add-type" title="{{__('Add Type')}}" save-style="success" save-text="Add">
        <x-form name="types">
            <x-field
                label="{{ __('Name') }}"
                name="name"
                required
                type="text"
            />
        </x-form>
    </x-modal>
@endsection
