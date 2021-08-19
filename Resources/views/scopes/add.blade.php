@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            $module->getName(),
            $module_base.'scopes' => __('Scopes'),
            __('Add Scope')
        ),
    )
)
@section('content')
    <x-form name="scope">
        <x-card >
            <x-field
                name="name"
                label="{{__('Name')}}"
                type="text"
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
