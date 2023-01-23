@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>___('dictionary.management'),
            $module->getName(),
            $module_base.'customers' => ___('Customers'),
            $customer->name
        ),
        'page_menu'=> $page_menu
    )
)
@section('content')
    <x-card :details="$customer_details"/>
    <x-tabs name="customer" :tabs="$tabs">
        <x-tab target="{{___('Details')}}">
            <x-form name="customer">
                <x-card>
                    <div class="grid-md-2">
                        <x-field
                            name="name"
                            label="dictionary.name"
                            type="text"
                            value="{{ $customer->name}}"
                            required
                        />
                        <x-field
                            label="dictionary.reference"
                            name="reference"
                            note="{{ ___('aegis::messages.customer.reference-message') }}"
                            required
                            type="text"
                            value="{{ $customer->reference}}"
                        />
                    </div>
                </x-card>
                <x-field type="actions">
                    <x-slot name="center">
                        <x-field label="{{ ___('Update') }}" name="add" type="submit" style="primary"/>
                    </x-slot>
                </x-field>
            </x-form>
        </x-tab>
        <x-tab target="{{___('Projects')}}">
            <x-card-view
                d-customer-id="{{ $customer->id }}"
                model="Project"
                module="AEGIS"
                view="row"
            />
        </x-tab>
    </x-tabs>
    @include(
        'aegis::_partials.modals.merge',
        compact(
            'customers'
        )
    )
@endsection
