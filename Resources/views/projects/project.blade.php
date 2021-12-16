@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            $module->getName(),
            $module_base.'projects' => __('dictionary.projects'),
            $project->id.': '.$project->name
        ),
        'page_menu'=> array(
            array(
                'href' =>'/a/m/AEGIS/projects/add-variant/'.$project->id,
                'icon' =>'file-plus',
                'title'=>__('phrases.add',['item'=>__('dictionary.variant')])
            ),
        )
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
                        required
                        type="textarea"
                        value="{{$project->description}}"
                    />
                    <x-field
                        controller="Scopes"
                        label="{{__('dictionary.scope')}}"
                        name="scope"
                        type="autocomplete"
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
                        disabled="{{ true }}"
                        label="{{__('dictionary.reference')}}"
                        name="reference"
                        type="text"
                        value="{{$project->reference}}"
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
                    <x-form name="{{ 'default'.$variant->id }}" action="{{'/a/m/AEGIS/projects/variant/'.$variant->id}}">
                        <x-card>
                            <x-field
                                label="{{__('dictionary.name')}}"
                                name="name"
                                required
                                type="text"
                                value="{{$variant->name}}"
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
                    @if($documents_module_enabled)
                        <div class="text-center">
                            <x-link style="primary" title="{{__('aegis::projects.add-document')}}" href="{{ url('a/m/Documents/document/add?project_variant='.$variant->id)}}"/>
                        </div>
                    @endif
                </x-tab>
            @else
                <x-tab target="{{ __('dictionary.variant').' '.$i.' ('.$variant->name.')' }}">
                    <x-form name="{{ 'variant'.$variant->id }}" action="{{'/a/m/AEGIS/projects/variant/'.$variant->id}}">
                        <x-card>
                            <x-field
                                label="{{__('dictionary.name')}}"
                                name="name"
                                required
                                type="text"
                                value="{{$variant->name}}"
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
