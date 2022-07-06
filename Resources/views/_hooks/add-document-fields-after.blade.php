<x-field
    hidden
    label="aegis::phrases.feedback-list-type"
    name="aegis[feedback-list-type]"
    :options="$feedback_list_types"
    type="select"
    :value="isset($document) ? $document->getMeta('feedback_list_type_id') : null"
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
@if(isset($document) && $document->status !== 'Draft')
@else
    <x-field
        label="aegis::phrases.submit-as-role"
        name="aegis[author-role]"
        :options="$job_titles"
        required
        type="select"
        :value="isset($document) ? $document->getMeta('author_role') : null"
    />
    @isset($projects)
        <x-field
            label="dictionary.reference"
            min="1"
            name="aegis[reference]"
            :prefield="$selected_variant ? $selected_variant->reference.'/'.($document->category->prefix ?? '') : '&hellip;'"
            required
            type="text"
            :value="isset($reference) ? str_replace($selected_variant ? $selected_variant->reference.'/'.($document->category->prefix ?? '') : '', '', $reference) : null"
        />
        <x-field
            disabled="true"
            label="dictionary.issue"
            name="aegis[issue]"
            type="number"
            :value="$document_variant ? $document_variant->issue : 1"
        />
    @endisset
@endif
