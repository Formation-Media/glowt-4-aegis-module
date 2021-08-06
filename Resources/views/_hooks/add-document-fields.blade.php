
@isset($project_variants)
    <x-field
        label="{{ __('Project Variant') }}"
        name="aegis[project_variant]"
        :options="$project_variants"
        type="select"
        :value="$selected_variant??''"
    />
@endisset

