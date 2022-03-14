@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            $module->getName(),
            $module_base.'projects' => 'dictionary.projects',
            $project->reference.': '.$project->name
        ),
        'page_menu'=> array(
            array(
                'href' =>'/a/m/AEGIS/projects/add-variant/'.$project->id,
                'icon' =>'file-plus',
                'title'=>['phrases.add',['item'=>___('dictionary.variant')]]
            ),
        )
    )
)
@section('content')
    <x-card :details="$project->details" />
    <x-tabs name="project" :tabs="$tabs">
        <x-tab target="{{___('dictionary.details')}}">
            <x-form name="project">
                <x-card body-class="grid-md-2">
                    <x-field
                        label="{{___('dictionary.name')}}"
                        name="name"
                        type="text"
                        value="{{$project->name}}"
                        required
                    />
                    <x-field
                        controller="Customers"
                        label="{{___('dictionary.customer')}}"
                        name="customer"
                        type="autocomplete"
                        method="customers"
                        module="AEGIS"
                        :value="[
                            'text'  => $customer->name ?? null,
                            'value' => $customer->id ?? null
                        ]"
                        required
                    />
                    <x-field
                        label="{{___('dictionary.description')}}"
                        name="description"
                        type="textarea"
                        value="{{$project->description}}"
                    />
                    <x-field
                        label="{{___('dictionary.type')}}"
                        name="type"
                        type="select"
                        :options="$types"
                        value="{{$project->type->id?? null}}"
                        required
                    />
                    <x-field
                        disabled="{{ true }}"
                        label="{{___('dictionary.reference')}}"
                        name="reference"
                        type="text"
                        value="{{$project->reference}}"
                    />
                </x-card>
                <x-field type="actions">
                    <x-slot name="center">
                        <x-field label="{{ ___('dictionary.update') }}" name="add" type="submit" style="primary"/>
                    </x-slot>
                </x-field>
            </x-form>
        </x-tab>
        @foreach($variants as $i=>$variant)
            @if ($variant->is_default==true)
                <x-tab target="{!! ___('dictionary.default').' ('.$variant->name.')' !!}">
                    <x-form name="{{ 'default'.$variant->id }}" action="{{'/a/m/AEGIS/projects/variant/'.$variant->id}}">
                        <x-card>
                            <x-field
                                label="dictionary.name"
                                name="name"
                                required
                                type="text"
                                value="{{$variant->name}}"
                            />
                            <x-field
                                disabled="{{true}}"
                                label="dictionary.reference"
                                name="reference"
                                type="text"
                                value="{{ $variant->reference }}"
                            />
                        </x-card>
                        <x-field type="actions">
                            <x-slot name="center">
                                <x-field label="{{ ___('dictionary.update') }}" name="add" type="submit" style="primary"/>
                            </x-slot>
                        </x-field>
                    </x-form>
                    <h2>{{___('dictionary.documents')}}</h2>
                    <x-table selects controller="Projects" module="AEGIS" method="variantdocumentsview" type="classic" id="{{$variant->id}}" />
                    @if($documents_module_enabled)
                        <div class="text-center">
                            <x-link style="primary" title="{{___('aegis::projects.add-document')}}" href="{{ url('a/m/Documents/document/add?project_variant='.$variant->id)}}"/>
                        </div>
                    @endif
                </x-tab>
            @else
                <x-tab target="{!! ___('dictionary.variant').' '.$i.' ('.$variant->name.')' !!}">
                    <x-form name="{{ 'variant'.$variant->id }}" action="{{'/a/m/AEGIS/projects/variant/'.$variant->id}}">
                        <x-card>
                            <x-field
                                label="dictionary.name"
                                name="name"
                                required
                                type="text"
                                value="{{$variant->name}}"
                            />
                            <x-field
                                disabled="{{true}}"
                                label="dictionary.reference"
                                name="reference"
                                type="text"
                                value="{{$variant->reference}}"
                            />
                            <x-field
                                disabled="{{true}}"
                                label="aegis::projects.variant-number"
                                name="variant_number"
                                type="number"
                                value="{{$variant->variant_number}}"
                            />
                            <x-field
                                disabled="{{true}}"
                                label="dictionary.description"
                                name="description"
                                type="textarea"
                                value="{{$variant->description}}"
                            />
                        </x-card>
                        <x-field type="actions">
                            <x-slot name="center">
                                <x-field label="{{ ___('dictionary.update') }}" name="add" type="submit" style="primary"/>
                            </x-slot>
                        </x-field>
                    </x-form>
                    <h2>{{___('dictionary.documents')}}</h2>
                    <x-table selects controller="Projects" module="AEGIS" method="variantdocumentsview" type="classic" id="{{$variant->id}}" />
                    @if($documents_module_enabled)
                        <div class="text-center">
                            <x-link style="primary" title="{{___('aegis::projects.add-document')}}" href="{{ url('a/m/Documents/document/add?project_variant='.$variant->id)}}"/>
                        </div>
                    @endif
                </x-tab>
            @endif
        @endforeach
    </x-tabs>
@endsection
