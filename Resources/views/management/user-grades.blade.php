@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>___('dictionary.management'),
            $module->getName(),
            ___('User Grades')
        ),
        'page_menu'=>array(
            array(
                'data' =>array(
                    'bs-target'=>'#modal-add-user-grade',
                    'bs-toggle'=>'modal'
                ),
                'icon' =>'plus',
                'title'=>'Add User Grade'
            )
        ),
    )
)

@section('content')
    <x-table selects controller="management" method="user-grades" module="AEGIS" />
    <x-modal id="add-user-grade" title="Add User Grade" save-style="success" save-text="Add">
        <x-form name="company">
            <x-field
                label="{{ ___('Grade Name') }}"
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
