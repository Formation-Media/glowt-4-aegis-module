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
        <x-card body-class="grid-md-2">
            <x-field
                label="dictionary.company"
                name="company_id"
                :options="$companies"
                required
                type="select"
            />
            <x-field
                label="aegis::phrases.project-title"
                name="name"
                required
                type="text"
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
                label="dictionary.description"
                name="description"
                type="textarea"
            />
            <x-field
                label="dictionary.type"
                name="type"
                :options="$types"
                required
                type="select"
            />
            <x-field
                label="aegis::phrases.project-number"
                max="{{ str_pad('', config('settings.aegis.project.character-limit'), 9) }}"
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
