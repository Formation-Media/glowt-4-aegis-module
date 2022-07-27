@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=> 'dictionary.management',
            $module_name,
            $module_base.'management/project-types' => 'aegis::phrases.project-types',
            $project_type->name
        )
    )
)
@section('content')
    <x-form name="customer">
        <x-card body-class="grid-md-2">
            <x-field
                label="dictionary.name"
                name="name"
                required
                type="text"
                value="{{ $project_type->name }}"
            />
        </x-card>
        <x-field type="actions">
            <x-slot name="center">
                <x-field label="dictionary.save" name="add" type="submit"/>
            </x-slot>
        </x-field>
    </x-form>
@endsection
