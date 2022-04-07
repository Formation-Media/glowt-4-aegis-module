@if($args['module'] == 'HR' && $args['method'] == 'table_competencies')
    <x-field
        label="dictionary.company"
        name="company"
        :options="$companies"
        placeholder="aegis::phrases.all-companies"
        type="select"
        value="{{ $args['request']['filter']['company'] ?? false }}"
    />
    {{-- <div class="align-self-end">
        <a class="btn btn-secondary w-100 js-achievements-search" title="Achievement Search">
            <i class="fal fa-magnifying-glass  fa-fw"></i>Achievement Search
        </a>
    </div> --}}
@endif
