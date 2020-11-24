@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>'Management',
                            __('User Grades')
        ),
        'page_menu'=>array(
            array(
                'data' =>array(
                    'target'=>'#modal-add-user-grade',
                    'toggle'=>'modal'
                ),
                'icon' =>'plus',
                'title'=>'Add User Grade'
            )
        ),
    )
)

@section('content')
    <x-table selects api="management" method="user-grades" module="AEGIS" />
    <x-modal id="add-user-grade" title="Add User Grade" save-style="success" save-text="Add">
        <x-form name="company">
            <x-field
                label="{{ __('Grade Name') }}"
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
