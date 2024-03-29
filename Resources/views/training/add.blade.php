@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'm/Documents/document'  => 'dictionary.documents',
            $module_base.'training' => 'aegis::dictionary.training',
            'dictionary.add',
        ),
    )
)
@section('content')
    <x-form name="project">
        <x-card body-class="grid-md-2">
            <x-field
                label="aegis::phrases.training-course-name"
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
                name="customer_id"
                required
                type="autocomplete"
                :value="[
                    'text'  => $customer->name ?? null,
                    'value' => $customer->id ?? null
                ]"
            />
            <x-field
                label="aegis::dictionary.presenter"
                name="presenter_id"
                :options="$users"
                required
                type="select"
                :value="$auth_user->id"
            />
            <x-field
                label="dictionary.description"
                name="description"
                required
                type="editor"
                wrapclass="span-2"
            />
            <x-field
                label="aegis::phrases.start-date"
                name="start_date"
                required
                type="date"
            />
            <x-field
                label="aegis::phrases.end-date"
                name="end_date"
                required
                type="date"
            />
            <div class="grid-2">
                <x-field
                    label="aegis::phrases.duration-length"
                    min="1"
                    name="duration_length"
                    required
                    type="number"
                />
                <x-field
                    label="aegis::phrases.duration-period"
                    name="duration_period"
                    :options="$training_names"
                    required
                    type="select"
                />
            </div>
            <x-field
                allow-add
                controller="training"
                label="dictionary.location"
                method="location"
                module="AEGIS"
                name="location"
                required
                type="autocomplete"
            />
            <x-field
                label="aegis::phrases.presentation-material"
                name="presentation"
                required
                type="editor"
            />
            <x-field
                label="dictionary.reference"
                name="reference"
                type="static"
                value="&hellip;"
            />
        </x-card>
        <x-field type="actions">
            <x-slot name="center">
                <x-field label="dictionary.add" name="add" type="submit" style="success"/>
            </x-slot>
        </x-field>
    </x-form>
@endsection
