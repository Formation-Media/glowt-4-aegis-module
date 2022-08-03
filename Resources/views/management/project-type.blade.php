@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array_merge(
            array(
                'management'=>___('dictionary.management'),
                $module->getName()
            ),
            $breadcrumbs
        ),
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
            <x-field
                label="aegis::dictionary.parent"
                name="parent_id"
                :options="$parent_tree"
                type="select"
                value="{{ $project_type->parent_id }}"
            />
        </x-card>
        <x-field type="actions">
            <x-slot name="center">
                <x-field label="dictionary.save" name="add" type="submit"/>
            </x-slot>
        </x-field>
    </x-form>
@endsection
