@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>___('dictionary.management'),
            $module->getName(),
            ___('Job Titles')
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
                label="{{ ___('Job Title') }}"
                name="name"
                required
                type="text"
            />
            <x-field
                checked
                label="{{ ___('Status') }}"
                name="status"
                type="switch"
            />
        </x-form>
    </x-modal>
@endsection
