@isset($projects)
    <x-field
        disabled="{{$selected_project? true: false}}"
        label="{{__('Project')}}"
        name="aegis[project]"
        :options="$projects"
        type="select"
        :value="$selected_project? $selected_project->id :''"
        required
    />
    <x-field
        disabled="{{$selected_variant? true : false}}"
        label="{{ __('Project Variant') }}"
        name="aegis[project_variant]"
        :options="$project_variants ?? [] "
        type="select"
        :value="$selected_variant? $selected_variant->id : ''"
        required
    />
    <x-field
        disabled="{{true}}"
        label="{{__('dictionary.reference')}}"
        name="aegis[reference]"
        type="text"
        value="{{$reference?? null}}"
    />
@endisset
