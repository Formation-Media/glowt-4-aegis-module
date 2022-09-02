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
    <x-card-view
        id="{{ $project_type ? $project_type->id : null }}"
        model="Type"
        module="AEGIS"
        view="row"
    />
    <x-modal id="add-type" title="{{___('Add Type')}}" save-style="success" save-text="Add">
        <x-form name="types">
            <x-field
                label="dictionary.name"
                name="name"
                required
                type="text"
            />
            <x-field
                label="aegis::dictionary.parent"
                name="parent_id"
                :options="$parent_tree"
                placeholder="dictionary.none"
                type="select"
            />
        </x-form>
    </x-modal>
@endsection
