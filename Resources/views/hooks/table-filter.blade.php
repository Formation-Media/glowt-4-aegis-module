@if($args['module']=='HR' && $args['method']=='competencies')
    <x-field
        disabled
        label="{{ __('Company') }}"
        name="company"
        :options="$companies"
        placeholder="All Companies"
        type="select"
    />
@endif
