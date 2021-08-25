@if(sizeof($job_titles))
    <x-field
        disabled="{{ $method==='profile' }}"
        label="{{ __('Job Title') }}"
        name="aegis[discipline]"
        :options="$job_titles"
        required
        type="select"
        value="{{ isset($user)?$user->getMeta('aegis.discipline'):false }}"
    />
@endif
@if(sizeof($grades))
    <x-field
        disabled="{{ $method==='profile' }}"
        label="{{ __('Grade') }}"
        name="aegis[grade]"
        :options="$grades"
        required
        type="select"
        value="{{ isset($user) && sizeof($user->meta) && $user->getMeta('aegis.grade')?$user->getMeta('aegis.grade'):'' }}"
    />
@endif
<x-field
    disabled="{{ $method==='profile' }}"
    label="{{ __('Type') }}"
    name="aegis[type]"
    :options="$types"
    required
    type="select"
	value="{{ isset($user) && sizeof($user->meta) && $user->getMeta('aegis.type')?$user->getMeta('aegis.type'):'' }}"
/>
