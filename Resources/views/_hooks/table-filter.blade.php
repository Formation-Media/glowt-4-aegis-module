@if($args['module']=='HR' && $args['method']=='table_competencies')
    @php
        $company=isset($args['request']['filter']['company'])?$args['request']['filter']['company']:false;
    @endphp
    <x-field
        label="{{ __('Company') }}"
        name="company"
        :options="$companies"
        placeholder="All Companies"
        type="select"
        value="{{ $company }}"
    />
@endif
