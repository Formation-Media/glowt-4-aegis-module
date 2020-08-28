@if (sizeof($suppliers))
    <x-field
        label="{{ __('Competency Type') }}"
        name="aegis[type]"
        :options="$suppliers"
        required
        type="select"
    />
@endif
