@if (count($companies))
    <x-field
        disabled="{{ isset($competency) && $competency->status>2 }}"
        label="{{ ___('Competency Company') }}"
        name="aegis[company]"
        :options="$companies"
        required
        type="select"
        value="{{ isset($value)?$value:null }}"
    />
@endif
@isset($competency)
    <div class="span-2">
        <hr>
        <x-details :details="$details" />
        <hr>
        <strong>Pen Profile:</strong><br>
        <x-blockquote>{!! $bio !!}</x-blockquote>
    </div>
@endisset
