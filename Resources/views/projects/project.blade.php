@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'm/Documents/document'  => 'dictionary.documents',
            $module_base.'projects' => 'dictionary.projects',
            $project->reference.': '.$project->name
        )
    )
)
@section('content')
    <x-card :details="$project->details" />
    <x-tabs name="project" :tabs="$tabs">
        <x-tab target="details">
            <x-form name="project">
                <x-card body-class="grid-md-2">
                    <x-field
                        label="aegis::phrases.project-title"
                        name="name"
                        type="text"
                        value="{{$project->name}}"
                        required
                    />
                    <x-field
                        controller="Customers"
                        label="dictionary.customer"
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
                        label="dictionary.description"
                        name="description"
                        type="textarea"
                        value="{{$project->description}}"
                    />
                    <x-field
                        label="dictionary.type"
                        name="type"
                        type="select"
                        :options="$types"
                        value="{{$project->type->id?? null}}"
                        required
                    />
                    <x-field
                        disabled="{{ !($auth_user->is_administrator || $auth_user->is_manager) }}"
                        label="aegis::phrases.project-number"
                        name="reference"
                        required="{{ $auth_user->is_administrator || $auth_user->is_manager }}"
                        type="text"
                        value="{{ $project->reference }}"
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
                <x-tab target="default">
                    <x-form name="{{ 'default'.$variant->id }}" action="{{'/a/m/AEGIS/projects/phase/'.$variant->id}}">
                        <x-card body-class="grid-md-2">
                            <x-field
                                label="dictionary.name"
                                name="name"
                                required
                                type="text"
                                value="{{ $variant->name }}"
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
                            <x-link style="primary" title="aegis::phrases.add-document" href="{{ url('a/m/Documents/document/add?project_phase='.$variant->id)}}"/>
                        </div>
                    @endif
                </x-tab>
            @else
                <x-tab target="phase-{{ $i }}">
                    <x-form name="{{ 'variant'.$variant->id }}" action="{{'/a/m/AEGIS/projects/phase/'.$variant->id}}">
                        <x-card body-class="grid-md-2">
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
                                label="aegis::phrases.phase-number"
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
                            <x-link style="primary" title="aegis::phrases.add-document" href="{{ url('a/m/Documents/document/add?project_phase='.$variant->id)}}"/>
                        </div>
                    @endif
                </x-tab>
            @endif
        @endforeach
    </x-tabs>
@endsection
