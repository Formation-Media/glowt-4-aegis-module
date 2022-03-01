@if($args['module'] == 'HR' && $args['method'] == 'table_competencies')
    <x-field
        label="dictionary.company"
        name="company"
        :options="$companies"
        placeholder="aegis::phrases.all-companies"
        type="select"
        value="{{ $company }}"
    />
@endif
