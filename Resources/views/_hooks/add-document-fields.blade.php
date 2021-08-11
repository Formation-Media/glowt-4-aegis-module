@isset($projects)
    <x-field
        label="{{__('Project')}}"
        name="aegis[project]"
        :options="$projects"
        type="select"
        :value="$project??''"
    />

    <x-field
        label="{{ __('Project Variant') }}"
        name="aegis[project_variant]"
        :options="[]"
        type="select"
        :value="$selected_variant??''"
    />
@endisset



