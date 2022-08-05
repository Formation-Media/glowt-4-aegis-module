@if($args['module'] == 'Documents' && $args['model'] == 'Document' &&
    !($route['module'] === 'AEGIS' && $route['feature'] === 'projects' && $route['view'] === 'project')
)
    <x-field
        controller="projects"
        label="dictionary.project"
        method="projects"
        module="AEGIS"
        name="project"
        type="autocomplete"
    />
    <x-field
        label="aegis::dictionary.phase"
        disabled
        name="phase"
        :options="[]"
        type="select"
    />
@endif
@if (!$route['module'] && $route['feature'] === 'users' && $route['view'] === 'user')
    <x-field
        label="dictionary.role"
        name="role"
        :options="$roles"
        type="select"
    />
@endif
