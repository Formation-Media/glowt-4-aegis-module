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
                        name="name"
                        label="{{__('dictionary.name')}}"
                        type="text"
                        value="{{$project->name}}"
                        required
                    />
                    <x-field
                        controller="Scopes"
                        name="scope"
                        label="{{__('dictionary.scope')}}"
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
                        name="type"
                        label="{{__('dictionary.type')}}"
                        type="select"
                        :options="$types"
                        value="{{$project->type->id?? null}}"
                        required
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
                        <x-link style="primary" title="{{ __('phrases.add',['item'=>__('dictionary.document')]) }}" href="{{ url('a/m/Documents/document/add?project_variant='.$variant->id) }}"/>
                    </div>
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
