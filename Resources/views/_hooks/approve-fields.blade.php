<x-field
    label="{{ ___('aegis::phrases.approve-as-role') }}"
    name="aegis[role]"
    :options="$job_titles"
    required
    type="select"
/>
