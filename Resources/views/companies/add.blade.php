@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            str_replace($module->getName(),'HR',$module_base)                       => 'HR',
            str_replace($module->getName(),'HR',$module_base).'competencies'        => __('dictionary.competencies'),
            str_replace($module->getName(),'HR',$module_base).'competencies/set-up' => __('dictionary.set-up'),
            $module_base.'companies'                                                => __('dictionary.companies'),
            __('dictionary.add')
        )
    )
)

@section('content')
    <x-form name="company">
        <x-card>
            <div class="grid-md-2">
                <x-field
                    label="{{ __('dictionary.name') }}"
                    name="name"
                    required
                    type="text"
                />
                <x-field
                    label="{{ __('dictionary.abbreviation') }}"
                    name="abbreviation"
                    required
                    type="text"
                />
                <x-field
                    accept=".pdf"
                    label="{{ __('aegis::phrases.pdf-footer') }}"
                    name="pdf_footer"
                    type="file"
                />
                <x-field
                    label="{{ __('dictionary.status') }}"
                    name="status"
                    type="switch"
                />
            </div>
        </x-card>
        <x-field type="actions">
            <x-slot name="right">
                <x-field label="{{ __('dictionary.add') }}" name="save" type="submit" style="primary"/>
            </x-slot>
        </x-field>
    </x-form>
@endsection
