@php
    $page_menu=array();
    $page_menu[]=array(
        'href' =>'/a/m/AEGIS/projects/add-variant/'.$project->id,
        'icon' =>'file-plus',
        'title'=>__('Add Variant')
    );

@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'projects' => __('Projects'),
                        $project->id.': '.$project->name
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
                        module="AEGIS"
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
        </x-tab>

        @foreach($variants as $i=>$variant)
            @if ($variant->is_default==true)
                <x-tab target="{{ __('Default ').' ('.$variant->name.')' }}">
                    <x-form name="{{ 'Default'.$variant->id }}" action="{{'/a/m/AEGIS/projects/variant/'.$variant->id}}">
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
                    <x-table selects api="Projects" module="AEGIS" method="variantdocumentsview" type="classic" id="{{$variant->id}}" />
                </x-tab>
            @else
                <x-tab target="{{ __('Variant ').$i.' ('.$variant->name.')' }}">
                    <x-form name="{{ 'variant'.$variant->id }}" action="{{'/a/m/AEGIS/projects/variant/'.$variant->id}}">
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
                    <x-table selects api="Projects" module="AEGIS" method="variantdocumentsview" type="classic" id="{{$variant->id}}" />
                </x-tab>
            @endif


        @endforeach

    </x-tabs>

@endsection
