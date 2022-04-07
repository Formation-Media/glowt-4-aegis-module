@if(isset($document) && $document->status !== 'Draft')
@else
    <x-field
        hidden
        label="aegis::phrases.feedback-list-type"
        name="aegis[feedback-list-type]"
        :options="$feedback_list_types"
        type="select"
    />
    <x-field
        :hidden="isset($document) && $document->category->prefix !== 'FBL' || !isset($document)"
        label="aegis::phrases.final-feedback-list"
        name="aegis[final-feedback-list]"
        :options="$yes_no"
        :required="isset($document) && $document->category->prefix === 'FBL'"
        type="select"
        :value="isset($document) ? $document->getMeta('final_feedback_list') : null"
    />
    <x-field
        label="aegis::phrases.submit-as-company"
        name="aegis[author-company]"
        :options="$companies"
        required
        type="select"
        :value="isset($document) ? $document->getMeta('author_company') : null"
    />
    <x-field
        label="aegis::phrases.submit-as-role"
        name="aegis[author-role]"
        :options="$job_titles"
        required
        type="select"
        :value="isset($document) ? $document->getMeta('author_role') : null"
    />
    @isset($projects)
        @isset($reference)
            <x-field
                label="dictionary.reference"
                name="aegis[documentreference]"
                type="text"
                value="{{$reference ?? null}}"
            />
        @else
            <x-field
                label="dictionary.reference"
                min="1"
                name="aegis[reference]"
                :prefield="$selected_variant ? $selected_variant->reference : '&hellip;'"
                required
                type="number"
            />
        @endisset
        <x-field
            disabled="true"
            label="dictionary.issue"
            name="aegis[issue]"
            type="number"
            value="1"
        />
    @endisset
@endif
