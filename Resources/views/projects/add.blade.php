@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            $module->getName(),
            $module_base.'projects' => 'dictionary.projects',
            'dictionary.add',
        ),
    )
)
@section('content')
    <x-form name="project">
        <x-card>
            <x-field
                label="dictionary.company"
                name="company_id"
                :options="$companies"
                required
                type="select"
            />
            <x-field
                label="dictionary.name"
                name="name"
                required
                type="text"
            />
            <x-field
                label="dictionary.description"
                name="description"
                type="textarea"
                required
            />
            <x-field
                allow-add
                controller="Customers"
                label="dictionary.customer"
                method="customers"
                module="AEGIS"
                name="customer"
                required
                type="autocomplete"
                :value="[
                    'text'  => $customer->name ?? null,
                    'value' => $customer->id ?? null
                ]"
            />
            <x-field
                label="dictionary.type"
                name="type"
                :options="$types"
                required
                type="select"
            />
            <x-field
                label="dictionary.reference"
                min="0"
                name="reference"
                prefield="{!! $customer ? $customer->reference : '&hellip;' !!}/"
                type="number"
                required
            />
        </x-card>
        <x-field type="actions">
            <x-slot name="center">
                <x-field label="dictionary.add" name="add" type="submit" style="success"/>
            </x-slot>
        </x-field>
    </x-form>
@endsection
