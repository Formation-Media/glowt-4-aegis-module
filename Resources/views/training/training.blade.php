@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            $module_base.'training' => 'aegis::dictionary.training',
            $training->title
        )
    )
)
@section('content')
    <x-card :details="$training->details" />
    <x-form name="training">
        <x-card body-class="grid-md-2">
            <x-field
                label="aegis::phrases.training-course-name"
                name="name"
                required
                type="text"
                :value="$training->name"
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
                    'text'  => $training->customer->name ?? null,
                    'value' => $training->customer->id ?? null
                ]"
            />
            <x-field
                label="aegis::dictionary.presenter"
                name="presenter_id"
                :options="$users"
                required
                type="select"
                :value="$training->presenter_id"
            />
            <x-field
                label="dictionary.description"
                name="description"
                required
                type="editor"
                wrapclass="span-2"
                :value="$training->description"
            />
            <x-field
                label="aegis::phrases.start-date"
                name="start_date"
                required
                type="date"
                :value="$training->start_date"
            />
            <x-field
                label="aegis::phrases.end-date"
                name="end_date"
                required
                type="date"
                :value="$training->end_date"
            />
            <div class="grid-2">
                <x-field
                    label="aegis::phrases.duration-length"
                    min="1"
                    name="duration_length"
                    required
                    type="number"
                    :value="$training->duration_length"
                />
                <x-field
                    label="aegis::phrases.duration-period"
                    name="duration_period"
                    :options="$training_names"
                    required
                    type="select"
                    :value="$training->duration_period"
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
                :value="[
                    'text'  => $training->location,
                    'value' => $training->location,
                ]"
            />
            <x-field
                label="aegis::phrases.presentation-material"
                name="presentation"
                required
                type="editor"
                :value="$training->presentation"
            />
            <x-field
                label="dictionary.reference"
                name="reference"
                type="static"
                value="&hellip;"
                :value="$training->reference"
            />
        </x-card>
        <x-field type="actions">
            <x-slot name="center">
                <x-field label="{{ ___('dictionary.update') }}" name="add" type="submit" style="primary"/>
            </x-slot>
        </x-field>
    </x-form>
@endsection
