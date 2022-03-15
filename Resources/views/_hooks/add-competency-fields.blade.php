@if (count($companies))
    <x-field
        disabled="{{ isset($competency) && $competency->status>2 }}"
        label="aegis::phrases.competency-company"
        name="aegis[company]"
        :options="$companies"
        required
        type="select"
        value="{{ isset($competency_details->company_id)?$competency_details->company_id:null }}"
    />
@endif
<x-field
    label="aegis::phrases.live-document"
    name="aegis[live-document]"
    type="url"
    value="{{ $live_document }}"
/>
@isset($competency)
    <div class="span-2">
        <hr>
        <x-details :details="$details" />
        <hr>
        <strong>Pen Profile:</strong><br>
        <x-blockquote>{!! $bio !!}</x-blockquote>
    </div>
@endisset
