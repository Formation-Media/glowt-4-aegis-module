@if(count($job_titles))
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
@if(count($grades))
    <x-field
        disabled="{{ $method==='profile' }}"
        label="{{ __('Grade') }}"
        name="aegis[grade]"
        :options="$grades"
        required
        type="select"
        value="{{ isset($user) && count($user->meta) && $user->getMeta('aegis.grade')?$user->getMeta('aegis.grade'):'' }}"
    />
@endif
<x-field
    disabled="{{ $method==='profile' }}"
    label="{{ __('Type') }}"
    name="aegis[type]"
    :options="$types"
    required
    type="select"
	value="{{ isset($user) && count($user->meta) && $user->getMeta('aegis.type')?$user->getMeta('aegis.type'):'' }}"
/>
@if($method!=='profile')
    <x-field
        label="{{ __('aegis::phrases.live-document') }}"
        name="aegis[live-document]"
        type="url"
        value="{{ isset($user) && count($user->meta) && $user->getMeta('aegis.live-document')?$user->getMeta('aegis.live-document'):'' }}"
    />
    @if($competency_sections)
        <x-field
            label="{{ __('aegis::phrases.default-sections') }}"
            name="aegis[default-sections][]"
            multiple
            :options="$competency_sections"
            type="select"
            :value="isset($user) && count($user->meta) && $user->getMeta('aegis.default-sections') ? $user->getMeta('aegis.default-sections'):''"
        />
    @endif
@endif
