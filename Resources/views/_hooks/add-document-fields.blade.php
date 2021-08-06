
@isset($project_variants)
    <x-field
        label="{{ __('Project Variants') }}"
        name="aegis[project_variant]"
        :options="$project_variants"
        type="select"
        :value="$selected_variant??''"
    />
@endisset

