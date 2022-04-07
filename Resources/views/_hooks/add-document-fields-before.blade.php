@if(isset($document) && $document->status !== 'Draft')
    <x-details :details="[
        'dictionary.project'              => $selected_project->title,
        'aegis::projects.project-variant' => $selected_variant->name,
    ]" />
@else
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
            label="Project Variant"
            name="aegis[project_variant]"
            :options="$project_variants ?? [] "
            type="select"
            :value="$selected_variant ? $selected_variant->id : ''"
            required
        />
    @endisset
@endif
