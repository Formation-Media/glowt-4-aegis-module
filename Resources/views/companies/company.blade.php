@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            str_replace($module->getName(),'HR',$module_base)   =>'HR',
            str_replace($module->getName(),'HR',$module_base).
                'competencies'                                  =>__('Competencies'),
            str_replace($module->getName(),'HR',$module_base).
                'competencies/set-up'                           =>__('Set-up'),
            $module_base.'companies'                            =>__('Companies'),
                                                                  $company->name
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
    <x-form name="company">
        <x-card>
            <div class="row">
                <div class="col-md-6">
                    <x-field
                        label="{{ __('Name') }}"
                        name="name"
                        required
                        type="text"
                        value="{{ $company->name }}"
                    />
                    <x-field
                        checked="{{ $company->status?true:false }}"
                        label="{{ __('Status') }}"
                        name="status"
                        type="switch"
                    />
                </div>
            </div>
        </x-card>
        <x-field type="actions">
            <x-slot name="right">
                <x-field label="{{ __('Update') }}" name="save" type="submit" style="primary"/>
            </x-slot>
        </x-field>
    </x-form>
@endsection
