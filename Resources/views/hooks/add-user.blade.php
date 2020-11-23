<x-field
    disabled="{{ $method==='profile' }}"
    api="user"
    label="{{ __('Key Discipline') }}"
    method="disciplines"
    module="AEGIS"
    name="aegis[discipline]"
    required
    type="text"
	value="{{ isset($user) && sizeof($user->meta) && $user->meta['aegis.discipline']?$user->meta['aegis.discipline']:'' }}"
/>
<x-field
    api="user"
    disabled="{{ $method==='profile' }}"
    label="{{ __('Grade') }}"
    method="grades"
    module="AEGIS"
    name="aegis[grade]"
    required
    type="text"
	value="{{ isset($user) && sizeof($user->meta) && $user->meta['aegis.grade']?$user->meta['aegis.grade']:'' }}"
/>
<x-field
    disabled="{{ $method==='profile' }}"
    label="{{ __('Type') }}"
    name="aegis[type]"
    :options="$types"
    required
    type="select"
	value="{{ isset($user) && sizeof($user->meta) && $user->meta['aegis.type']?$user->meta['aegis.type']:'' }}"
/>
