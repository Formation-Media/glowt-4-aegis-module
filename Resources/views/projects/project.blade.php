@php
    $page_menu=array();
    $page_menu[]=array(
        'href' =>'/a/m/AEGIS/projects/add-variant/'.$project->id,
        'icon' =>'file-plus',
        'title'=>__('aegis::projects.add-variant')
    );
@endphp
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            $module->getName(),
            $module_base.'projects' => __('dictionary.projects'),
                        $project->id.': '.$project->name
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')
    <x-tabs name="project" :tabs="$tabs">
        <x-tab target="{{__('dictionary.details')}}">
            <x-form name="project">
                <x-card>
                    <x-field
                        label="{{__('dictionary.name')}}"
                        name="name"
                        type="text"
                        value="{{$project->name}}"
                        required
                    />
                    <x-field
                        label="{{__('dictionary.description')}}"
                        name="description"
                        type="textarea"
                        value="{{$project->description}}"
                        required
                    />
                    <x-field
                        label="{{__('dictionary.scope')}}"
                        name="scope"
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
                        label="{{__('dictionary.type')}}"
                        name="type"
                        type="select"
                        :options="$types"
                        value="{{$project->type->id?? null}}"
                        required
                    />
                    <x-field
                        label="{{__('dictionary.reference')}}"
                        name="reference"
                        type="text"
                        value="{{$project->reference}}"
                        disabled="{{true}}"
                    />
                </x-card>
                <x-field type="actions">
                    <x-slot name="center">
                        <x-field label="{{ __('dictionary.update') }}" name="add" type="submit" style="primary"/>
                    </x-slot>
                </x-field>
            </x-form>
        </x-tab>
        @foreach($variants as $i=>$variant)
            @if ($variant->is_default==true)
                <x-tab target="{{ __('dictionary.default').' ('.$variant->name.')' }}">
                    <x-form name="{{ 'Default'.$variant->id }}" action="{{'/a/m/AEGIS/projects/variant/'.$variant->id}}">
                        <x-card>
                            <x-field
                                label="{{__('dictionary.name')}}"
                                name="name"
                                type="text"
                                value="{{$variant->name}}"
                                required
                            />
                            <x-field
                                disabled="{{true}}"
                                label="{{__('dictionary.reference')}}"
                                name="reference"
                                type="text"
                                value="{{$variant->reference}}"
                            />
                        </x-card>
                        <x-field type="actions">
                            <x-slot name="center">
                                <x-field label="{{ __('dictionary.update') }}" name="add" type="submit" style="primary"/>
                            </x-slot>
                        </x-field>
                    </x-form>
                    <h2>{{__('dictionary.documents')}}</h2>
                    <x-table selects controller="Projects" module="AEGIS" method="variantdocumentsview" type="classic" id="{{$variant->id}}" />
                    <div class="text-center">
                        <x-link style="primary" title="{{__('aegis::projects.add-document')}}" href="{{ url('a/m/Documents/document/add?project_variant='.$variant->id)}}"/>
                    </div>
                </x-tab>
            @else
                <x-tab target="{{ __('aegis::projects.variant').' '.$variant->variant_number.' ('.$variant->name.')' }}">
                    <x-form name="{{ 'variant'.$variant->id }}" action="{{'/a/m/AEGIS/projects/variant/'.$variant->id}}">
                        <x-card>
                            <x-field
                                name="name"
                                label="{{__('dictionary.name')}}"
                                type="text"
                                value="{{$variant->name}}"
                                required
                            />
                            <x-field
                                disabled="{{true}}"
                                label="{{__('dictionary.reference')}}"
                                name="reference"
                                type="text"
                                value="{{$variant->reference}}"
                            />
                            <x-field
                                disabled="{{true}}"
                                label="{{__('aegis::projects.variant-number')}}"
                                name="variant_number"
                                type="number"
                                value="{{$variant->variant_number}}"
                            />
                        </x-card>
                        <x-field type="actions">
                            <x-slot name="center">
                                <x-field label="{{ __('dictionary.update') }}" name="add" type="submit" style="primary"/>
                            </x-slot>
                        </x-field>
                    </x-form>
                    <h2>{{__('dictionary.documents')}}</h2>
                    <x-table selects controller="Projects" module="AEGIS" method="variantdocumentsview" type="classic" id="{{$variant->id}}" />
                </x-tab>
            @endif
        @endforeach
    </x-tabs>
@endsection
