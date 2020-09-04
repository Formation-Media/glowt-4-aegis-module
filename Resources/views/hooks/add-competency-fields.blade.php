@if (sizeof($suppliers))
    <x-field
        disabled="{{ isset($competency) && $competency->status>2 }}"
        label="{{ __('Competency Type') }}"
        name="aegis[supplier]"
        :options="$suppliers"
        required
        type="select"
        value="{{ isset($value)?$value:null }}"
    />
@endif
