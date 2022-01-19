@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            $module->getName(),
            $module_base.'projects' => __('dictionary.projects'),
            __('dictionary.add')
        ),
    )
)
@section('content')
    <x-form name="project">
        <x-card>
            <x-field
                label="{{__('dictionary.company')}}"
                name="company_id"
                :options="$companies"
                required
                type="select"
            />
            <x-field
                label="{{__('dictionary.name')}}"
                name="name"
                required
                type="text"
            />
            <x-field
                label="{{__('dictionary.description')}}"
                name="description"
                type="textarea"
                required
            />
            <x-field
                allow-add
                controller="Scopes"
                label="{{__('dictionary.scope')}}"
                method="scopes"
                module="AEGIS"
                name="scope"
                required
                type="autocomplete"
                :value="[
                    'text'  => $scope->name ?? null,
                    'value' => $scope->id ?? null
                ]"
            />
            <x-field
                label="{{__('dictionary.type')}}"
                name="type"
                :options="$types"
                required
                type="select"
            />
            <x-field
                label="{{__('dictionary.reference')}}"
                min="0"
                name="reference"
                prefield="{{$scope ? $scope->reference : '???'}}/"
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
