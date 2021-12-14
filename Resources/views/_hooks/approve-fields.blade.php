<x-field
    label="{{ __('dictionary.company') }}"
    name="aegis[company]"
    :options="$companies"
    required
    type="select"
/>
<x-field
    label="{{ __('aegis::phrases.approve-as-role') }}"
    name="aegis[role]"
    :options="$job_titles"
    required
    type="select"
/>
