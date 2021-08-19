@isset($projects)
    <x-field
        label="{{__('Project')}}"
        name="aegis[project]"
        :options="$projects"
        type="select"
        :value="$selected_project? $selected_project->id :''"
    />

    <x-field
        label="{{ __('Project Variant') }}"
        name="aegis[project_variant]"
        :options="$project_variants ?? [] "
        type="select"
        :value="$selected_variant? $selected_variant->id : ''"
    />
@endisset
