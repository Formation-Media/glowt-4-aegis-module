@if (sizeof($suppliers))
    <x-field
        label="{{ __('Competency Type') }}"
        name="aegis[supplier]"
        :options="$suppliers"
        required
        type="select"
        value="{{ isset($value)?$value:null }}"
    />
@endif
