@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management' => ___('dictionary.management'),
            $module->getName(),
            $module_base.'companies' => ___('dictionary.companies'),
            ___('dictionary.add')
        )
    )
)

@section('content')
    <x-form name="company">
        <x-card>
            <div class="grid-md-2">
                <x-field
                    label="{{ ___('dictionary.name') }}"
                    name="name"
                    required
                    type="text"
                />
                <x-field
                    label="{{ ___('dictionary.abbreviation') }}"
                    name="abbreviation"
                    required
                    type="text"
                />
                <x-field
                    accept=".pdf"
                    label="{{ ___('aegis::phrases.pdf-footer') }}"
                    name="pdf_footer"
                    type="file"
                />
                <x-field
                    label="{{ ___('dictionary.status') }}"
                    name="status"
                    type="switch"
                />
                <x-field
                    label="aegis::_settings.show-for-mdss.title"
                    name="status"
                    note="aegis::_settings.show-for-mdss.description"
                    type="switch"
                />
            </div>
        </x-card>
        <x-field type="actions">
            <x-slot name="right">
                <x-field label="{{ ___('dictionary.add') }}" name="save" type="submit" style="primary"/>
            </x-slot>
        </x-field>
    </x-form>
@endsection
