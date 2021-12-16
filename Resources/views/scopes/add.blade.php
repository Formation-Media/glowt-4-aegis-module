@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>__('dictionary.management'),
            $module->getName(),
            $module_base.'scopes' => __('dictionary.scopes'),
            __('dictionary.add')
        ),
    )
)
@section('content')
    <x-form name="scope">
        <x-card >
            <x-field
                name="name"
                label="{{__('dictionary.name')}}"
                type="text"
                required
            />
            <x-field
                label="{{__('dictionary.reference')}}"
                name="reference"
                type="text"
                note="{{__('aegis::scopes.reference-message')}}"
                aria-readonly
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
