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
@isset($competency)
    <div class="span-2">
        <strong>Pen Profile:</strong><br>
        <x-blockquote>{!! $bio !!}</x-blockquote>
    </div>
@endisset
