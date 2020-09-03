<x-field
    api="user"
    label="{{ __('Key Discipline') }}"
    method="disciplines"
    module="Aegis"
    name="aegis[discipline]"
    required
    type="text"
/>
<x-field
    api="user"
    label="{{ __('Grade') }}"
    method="grades"
    module="Aegis"
    name="aegis[grade]"
    required
    type="text"
/>
{{--
<x-field
    api="user"
    label="{{ __('Key Discipline') }}"
    method="disciplines"
    module="Aegis"
    name="aegis[discipline]"
    required
    type="autocomplete"
    :value="[
        'id'  =>isset($qualification)?$qualification->discipline:'',
        'text'=>isset($qualification)?$qualification->discipline:''
    ]"
/>
<x-field
    api="user"
    label="{{ __('Grade') }}"
    method="grades"
    module="Aegis"
    name="aegis[grade]"
    required
    type="autocomplete"
/>
--}}
<x-field
    label="{{ __('Type') }}"
    name="aegis[type]"
    :options="$types"
    required
    type="select"
/>
