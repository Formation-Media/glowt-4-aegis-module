@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>___('dictionary.management'),
            $module->getName(),
            $module_base.'customers' => ___('dictionary.customers'),
            ___('dictionary.add')
        ),
    )
)
@section('content')
    <x-form name="customer">
        <x-card >
            <x-field
                name="name"
                label="{{___('dictionary.name')}}"
                type="text"
                required
            />
            <x-field
                aria-readonly
                label="{{___('dictionary.reference')}}"
                name="reference"
                note="{{___('aegis::customers.reference-message')}}"
                required
                type="text"
            />
        </x-card>
        <x-field type="actions">
            <x-slot name="center">
                <x-field label="{{ ___('dictionary.add') }}" name="add" type="submit" style="success"/>
            </x-slot>
        </x-field>
    </x-form>

@endsection
