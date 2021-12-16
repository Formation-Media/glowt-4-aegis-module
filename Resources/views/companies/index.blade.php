@php
    $page_menu=array();
    if($permissions['add']){
        $page_menu[]=array(
            'data' =>array(
                'bs-target'=>'#modal-add-competency-company',
                'bs-toggle'=>'modal'
            ),
            'icon' =>'plus',
            'title'=>'Add Competency Company'
        );
    }
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>__('dictionary.management'),
            $module->getName(),
            __('Companies')
        ),
        'page_menu'=>$page_menu,
    )
)

@section('content')
    <x-table selects controller="companies" method="companies" module="AEGIS" />
    @if($permissions['add'])
        <x-modal id="add-competency-company" title="Add Competency Company" save-style="success" save-text="Add">
            <x-form name="company">
                <x-field
                    label="{{ __('Company Name') }}"
                    name="name"
                    required
                    type="text"
                />
                <x-field
                    label="{{ __('dictionary.abbreviation') }}"
                    name="abbreviation"
                    required
                    type="text"
                />
                <x-field
                    checked
                    label="{{ __('Status') }}"
                    name="status"
                    type="switch"
                />
            </x-form>
        </x-modal>
    @endif
@endsection
