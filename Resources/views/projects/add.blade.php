@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            $module->getName(),
            $module_base.'projects' => __('Projects'),
            __('Add Project')
        ),
    )
)
@section('content')
    <x-form name="project">
        <x-card title="{{__('Details')}}">
            <x-field
                name="name"
                label="{{__('Name')}}"
                type="text"
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
                allow-add
                required
            />
            <x-field
                name="type"
                label="{{__('Type')}}"
                type="select"
                :options="$types"
                required
            />
        </x-card>
        <x-field type="actions">
            <x-slot name="center">
                <x-field label="{{ __('Add') }}" name="add" type="submit" style="success"/>
            </x-slot>
        </x-field>
    </x-form>
@endsection
