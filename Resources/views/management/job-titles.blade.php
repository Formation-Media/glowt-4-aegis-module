@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>__('dictionary.management'),
            $module->getName(),
            __('Job Titles')
        ),
        'page_menu'=>array(
            array(
                'data' =>array(
                    'bs-target'=>'#modal-add-job-title',
                    'bs-toggle'=>'modal'
                ),
                'icon' =>'plus',
                'title'=>'Add Job Title'
            )
        ),
    )
)

@section('content')
    <x-table selects controller="management" method="job-titles" module="AEGIS" />
    <x-modal id="add-job-title" title="Add Job Title" save-style="success" save-text="Add">
        <x-form name="company">
            <x-field
                label="{{ __('Job Title') }}"
                name="name"
                required
                type="text"
            />
            <x-field
                checked
                label="{{ __('Status') }}"
                name="status"
                type="switch"
            />
        </x-form>
    </x-modal>
@endsection
