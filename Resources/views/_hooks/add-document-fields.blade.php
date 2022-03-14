@if(isset($document) && $document->status !== 'Draft')
    <x-details :details="[
        'dictionary.project'                        => $selected_project->title,
        'aegis::projects.project-variant'           => $selected_variant->name,
        'aegis::projects.project-variant-reference' => '...',
    ]" />
@else
    @if (!$selected_project)
        <x-field
            hidden
            label="aegis::phrases.feedback-list-type"
            name="aegis[feedback-list-type]"
            :options="$feedback_list_types"
            type="select"
        />
    @endif
    <x-field
        :hidden="isset($document) && $document->category->prefix !== 'FBL' || !isset($document)"
        label="aegis::phrases.final-feedback-list"
        name="aegis[final-feedback-list]"
        :options="$yes_no"
        :required="isset($document) && $document->category->prefix === 'FBL'"
        type="select"
        :value="isset($document) ? $document->getMeta('final_feedback_list') : null"
    />
    @isset($projects)
        <x-field
            controller="projects"
            label="dictionary.project"
            method="projects"
            module="AEGIS"
            name="aegis[project]"
            required
            type="autocomplete"
            :value="$selected_project ? [
                'text'  => $selected_project->title,
                'value' => $selected_project->id,
            ] : null"
        />
        <x-field
            disabled="{{$selected_variant? true : false}}"
            label="{{ ___('Project Variant') }}"
            name="aegis[project_variant]"
            :options="$project_variants ?? [] "
            type="select"
            :value="$selected_variant ? $selected_variant->id : ''"
            required
        />
        @isset($reference)
            <x-field
                disabled="{{true}}"
                label="aegis::projects.document-reference"
                name="aegis[documentreference]"
                type="text"
                value="{{$reference?? null}}"
            />
        @else
            <x-field
                label="aegis::projects.project-variant-reference"
                min="1"
                name="aegis[reference]"
                prefield="&hellip;"
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
