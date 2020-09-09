@if (sizeof($companies))
    <x-field
        disabled="{{ isset($competency) && $competency->status>2 }}"
        label="{{ __('Competency Company') }}"
        name="aegis[company]"
        :options="$companies"
        required
        type="select"
        value="{{ isset($value)?$value:null }}"
    />
@endif
