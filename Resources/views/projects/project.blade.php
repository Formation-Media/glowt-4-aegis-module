@php
    $page_menu=array();
    $page_menu[]=array(
        'href' =>'/a/m/Aegis/projects/add-variant/'.$project->id,
        'icon' =>'file-plus',
        'title'=>__('Add Variant')
    );

    $types=[
        'Engineering' => 'Engineering',
        'HR'          =>'HR',
        'Rail'        =>'Rail'
    ];
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'projects' => __('Projects'),
                        $project->name
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')
    <x-tabs name="project" :tabs="$tabs">
        <x-tab target="Details">
            <x-form name="project">
                <x-card>
                    <x-field
                        name="name"
                        label="{{__('Name')}}"
                        type="text"
                        value="{{$project->name}}"
                        required
                    />
                    <x-field
                        name="scope"
                        label="{{__('Scope')}}"
                        type="autocomplete"
                        api="Scopes"
                        method="scopes"
                        module="Aegis"
                        :value="[
                            'text' => $scope->name ?? null,
                            'value' => $scope->id ?? null
                        ]"
                        required
                    />
                    <x-field
                        name="type"
                        label="{{__('Type')}}"
                        type="select"
                        :options="$types"
                        value="{{$project->type ?? null}}"
                        required
                    />
                </x-card>
                <x-field type="actions">
                    <x-slot name="center">
                        <x-field label="{{ __('Update') }}" name="add" type="submit" style="primary"/>
                    </x-slot>
                </x-field>
            </x-form>
            <h2>Documents</h2>
            <x-table selects api="Projects" module="Aegis" method="variantdocumentsview" type="classic" id="{{$default_variant->id}}" />

        </x-tab>
        @foreach($variants as $variant)
            <x-tab target="{{ $variant->name }}">
                <x-form name="{{ 'variant'.$variant->id }}" action="{{'/a/m/Aegis/projects/variant/'.$variant->id}}">
                    <x-card>
                        <x-field
                            name="name"
                            label="{{__('Name')}}"
                            type="text"
                            value="{{$variant->name}}"
                            required
                        />
                    </x-card>
                    <x-field type="actions">
                        <x-slot name="center">
                            <x-field label="{{ __('Update') }}" name="add" type="submit" style="primary"/>
                        </x-slot>
                    </x-field>
                </x-form>
                <h2>Documents</h2>
                <x-table selects api="Projects" module="Aegis" method="variantdocumentsview" type="classic" id="{{$variant->id}}" />
            </x-tab>
        @endforeach

    </x-tabs>

@endsection
