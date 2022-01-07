@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            str_replace($module->getName(),'HR',$module_base)                       => 'HR',
            str_replace($module->getName(),'HR',$module_base).'competencies'        => __('dictionary.competencies'),
            str_replace($module->getName(),'HR',$module_base).'competencies/set-up' => __('Set-up'),
            $module_base.'companies'                                                => __('dictionary.companies'),
            $company->name
        )
    )
)

@section('content')
    <x-form name="company">
        <x-card>
            <div class="grid-md-2">
                <x-field
                    label="{{ __('Name') }}"
                    name="name"
                    required
                    type="text"
                    value="{{ $company->name }}"
                />
                <x-field
                    label="{{ __('dictionary.abbreviation') }}"
                    name="abbreviation"
                    required
                    type="text"
                    value="{{ $company->abbreviation }}"
                />
                <x-field
                    accept=".pdf"
                    :existing="$company->pdf_footer ?? null"
                    label="{{ __('aegis::phrases.pdf-footer') }}"
                    name="pdf_footer"
                    type="file"
                />
                <x-field
                    checked="{{ $company->status?true:false }}"
                    label="{{ __('Status') }}"
                    name="status"
                    type="switch"
                />
            </div>
        </x-card>
        <x-field type="actions">
            <x-slot name="right">
                <x-field label="{{ __('Update') }}" name="save" type="submit" style="primary"/>
            </x-slot>
        </x-field>
    </x-form>
@endsection
