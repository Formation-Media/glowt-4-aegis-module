@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>___('dictionary.management'),
            $module->getName(),
            $module_base.'companies' => ___('dictionary.companies'),
            $company->name
        )
    )
)

@section('content')
    <x-form name="company">
        <x-card>
            <div class="grid-md-2">
                <x-field
                    label="{{ ___('Name') }}"
                    name="name"
                    required
                    type="text"
                    value="{{ $company->name }}"
                />
                <x-field
                    label="{{ ___('dictionary.abbreviation') }}"
                    name="abbreviation"
                    required
                    type="text"
                    value="{{ $company->abbreviation }}"
                />
                <x-field
                    accept=".pdf"
                    :existing="$company->pdf_footer ?? null"
                    label="{{ ___('aegis::phrases.pdf-footer') }}"
                    name="pdf_footer"
                    type="file"
                />
                <x-field
                    checked="{{ $company->status?true:false }}"
                    label="{{ ___('Status') }}"
                    name="status"
                    type="switch"
                />
            </div>
        </x-card>
        <x-field type="actions">
            <x-slot name="right">
                <x-field label="{{ ___('Update') }}" name="save" type="submit" style="primary"/>
            </x-slot>
        </x-field>
    </x-form>
@endsection
