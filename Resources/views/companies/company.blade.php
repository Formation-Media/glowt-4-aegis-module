@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>__('dictionary.management'),
            $module->getName(),
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
