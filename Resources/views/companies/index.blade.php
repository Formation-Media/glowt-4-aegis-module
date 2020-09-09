@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            str_replace($module->getName(),'HR',$module_base)   =>'HR',
            str_replace($module->getName(),'HR',$module_base).
                'competencies'                                  =>__('Competencies'),
            str_replace($module->getName(),'HR',$module_base).
                'competencies/set-up'                           =>__('Set-up'),
                                                                __('Companies')
        ),
        'page_menu'=>array(
            array(
                'data' =>array(
                    'target'=>'#modal-add-competency-company',
                    'toggle'=>'modal'
                ),
                'icon' =>'plus',
                'title'=>'Add Competency Company'
            )
        ),
    )
)

@section('content')
    <x-table selects api="companies" method="companies" module="AEGIS" />
    <x-modal id="add-competency-company" title="Add Competency Company" save-style="success" save-text="Add">
        <x-form name="company">
            <x-field
                label="{{ __('Company Name') }}"
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
