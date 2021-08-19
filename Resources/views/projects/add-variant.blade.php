
@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'AEGIS',
            $module_base.'projects' => __('Projects'),
            'project/'.$project->id => $project->name,
            __('Add Variant')
        ),
    )
)
@section('content')

    <x-form name="project_variant">
        <x-card>
            <x-field
                name="name"
                type="text"
                label="{{__('Name')}}"
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
