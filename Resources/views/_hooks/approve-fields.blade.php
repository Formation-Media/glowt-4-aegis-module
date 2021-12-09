<x-field
    label="{{ __('dictionary.company') }}"
    name="company"
    :options="$companies"
    required
    type="select"
/>
