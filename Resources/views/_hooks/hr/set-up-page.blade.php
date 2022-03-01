@if($permissions['index'])
    <x-card class="shadow" title="{{ __('Companies') }}">
        <p>Manage competency companies.</p>
        <x-slot name="footer">
            <x-link href="/a/m/AEGIS/companies" style="primary" title="Companies"/>
        </x-slot>
    </x-card>
@endif
