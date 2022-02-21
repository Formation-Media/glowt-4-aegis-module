@if($method!=='profile' && $competency_sections)
    <x-field
        label="{!! ___('aegis::phrases.default-sections') !!}"
        name="aegis[default-sections][]"
        multiple
        :options="$competency_sections"
        type="select"
        :value="isset($user) && count($user->meta) && $user->getMeta('aegis.default-sections') ? $user->getMeta('aegis.default-sections'):''"
    />
@endif
@if(count($grades))
    <x-field
        disabled="{{ $method==='profile' }}"
        label="{{ ___('dictionary.grade') }}"
        name="aegis[grade]"
        :options="$grades"
        required
        type="select"
        value="{{ isset($user) && count($user->meta) && $user->getMeta('aegis.grade')?$user->getMeta('aegis.grade'):'' }}"
    />
@endif
@if(count($job_titles))
    <x-field
        disabled="{{ $method==='profile' }}"
        label="{{ ___('aegis::phrases.job-title') }}"
        multiple
        name="aegis[discipline][]"
        :options="$job_titles"
        required
        type="select"
        :value="isset($user)?$user->getMeta('aegis.discipline'):[]"
    />
@endif
@if($method!=='profile')
    <x-field
        label="{{ ___('aegis::phrases.live-document') }}"
        name="aegis[live-document]"
        type="url"
        value="{{ isset($user) && count($user->meta) && $user->getMeta('aegis.live-document')?$user->getMeta('aegis.live-document'):'' }}"
    />
@endif
<x-field
    disabled="{{ $method==='profile' }}"
    label="{{ ___('dictionary.type') }}"
    name="aegis[type]"
    :options="$types"
    required
    type="select"
	value="{{ isset($user) && count($user->meta) && $user->getMeta('aegis.type')?$user->getMeta('aegis.type'):'' }}"
/>
@if($method!=='profile')
    <x-field
        label="{{ ___('aegis::phrases.user-reference') }}"
        max="3"
        name="aegis[user-reference]"
        type="text"
        value="{{ isset($user) && count($user->meta) && $user->getMeta('aegis.user-reference')?$user->getMeta('aegis.user-reference'):'' }}"
    />
@endif
