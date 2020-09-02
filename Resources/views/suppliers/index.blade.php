@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            str_replace($module->getName(),'HR',$module_base)   =>'HR',
            str_replace($module->getName(),'HR',$module_base).
                'competencies'                                  =>__('Competencies'),
            str_replace($module->getName(),'HR',$module_base).
                'competencies/set-up'                           =>__('Set-up'),
            __('Suppliers')
        ),
        'page_menu'=>array(
            array(
                'data' =>array(
                    'target'=>'#modal-add-competency-supplier',
                    'toggle'=>'modal'
                ),
                'icon' =>'plus',
                'title'=>'Add Competency Supplier'
            )
        ),
    )
)

@section('content')
    <x-table selects api="suppliers" method="suppliers" module="Aegis" />
    <x-modal id="add-competency-supplier" title="Add Competency Supplier" save-style="success" save-text="Add">
        <x-form name="supplier">
            <x-field
                label="{{ __('Supplier Name') }}"
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
