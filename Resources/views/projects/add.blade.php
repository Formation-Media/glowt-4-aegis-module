@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            $module->getName(),
            $module_base.'projects' => __('dictionary.projects'),
            __('phrases.add', ['item'=> __('dictionary.project')])
        ),
    )
)
@section('content')
    <x-form name="project">
        <x-card title="{{__('dictionary.details')}}">
            <x-field
                label="{{__('dictionary.name')}}"
                name="name"
                type="text"
                required
            />
            <x-field
                label="{{__('dictionary.description')}}"
                name="description"
                type="textarea"
                required
            />
            <x-field
                allow-add
                api="Scopes"
                label="{{__('dictionary.scope')}}"
                method="scopes"
                module="AEGIS"
                name="scope"
                required
                type="autocomplete"
                :value="[
                    'text' => $scope->name ?? null,
                    'value' => $scope->id ?? null
                ]"
            />
            <x-field
                label="{{__('dictionary.type')}}"
                name="type"
                type="select"
                :options="$types"
                required
            />
            <x-field
                label="{{__('dictionary.reference')}}"
                max="9999"
                min="0"
                name="reference"
                prefield="{{$scope? $scope->reference.'/' : '???/'}}"
                type="number"
                required
            />

        </x-card>
        <x-field type="actions">
            <x-slot name="center">
                <x-field label="{{ __('dictionary.add') }}" name="add" type="submit" style="success"/>
            </x-slot>
        </x-field>
    </x-form>
@endsection
