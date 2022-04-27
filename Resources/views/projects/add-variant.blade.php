@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'm/Documents/document'                        => 'dictionary.documents',
            $module_base.'projects'                       => ___('Projects'),
            $module_base.'projects/project/'.$project->id => $project->reference.': '.$project->name,
            ___('Add Variant')
        ),
    )
)
@section('content')
    <x-form name="project_variant">
        <x-card>
            <x-field
                name="name"
                type="text"
                label="dictionary.name"
                required
            />
            <x-field
                label="aegis::projects.variant-number"
                min="0"
                name="variant_number"
                required
                type="number"
            />
            <x-field
                label="dictionary.description"
                name="description"
                type="textarea"
            />
        </x-card>
        <x-field type="actions">
            <x-slot name="center">
                <x-field label="{{ ___('Add') }}" name="add" type="submit" style="success"/>
            </x-slot>
        </x-field>
    </x-form>
@endsection
