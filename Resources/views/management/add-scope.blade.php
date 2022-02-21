@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>___('dictionary.management'),
            $module->getName(),
            $module_base.'scopes' => ___('Scopes'),
            ___('Add Scope')
        ),
    )
)
@section('content')
    <x-form name="scope">
        <x-card >
            <x-field
                name="name"
                label="{{___('Name')}}"
                type="text"
                required
            />
        </x-card>
        <x-field type="actions">
            <x-slot name="center">
                <x-field label="{{ ___('Add') }}" name="add" type="submit" style="success"/>
            </x-slot>
        </x-field>
    </x-form>
@endsection
