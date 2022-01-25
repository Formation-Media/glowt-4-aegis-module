<x-field
    hidden
    label="aegis::phrases.feedback-list-type"
    name="aegis[feedback-list-type]"
    :options="$feedback_list_types"
    type="select"
/>
@isset($projects)
    <x-field
        label="dictionary.project"
        name="aegis[project]"
        :options="$projects"
        type="select"
        :value="$selected_project? $selected_project->id :''"
        required
    />
    <x-field
        disabled="{{$selected_variant? true : false}}"
        label="{{ __('Project Variant') }}"
        name="aegis[project_variant]"
        :options="$project_variants ?? [] "
        type="select"
        :value="$selected_variant? $selected_variant->id : ''"
        required
    />
    @isset($reference)
        <x-field
            disabled="{{true}}"
            label="{{__('aegis::projects.document-reference')}}"
            name="aegis[documentreference]"
            type="text"
            value="{{$reference?? null}}"
        />
    @else
        <x-field
            disabled="{{true}}"
            label="{{__('aegis::projects.project-variant-reference')}}"
            name="aegis[reference]"
            type="text"
        />
    @endisset
@endisset
