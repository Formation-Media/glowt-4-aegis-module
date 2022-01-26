@if($linkless_documents)
    <h2>{{ __('aegis::messages.linkless-documents') }}</h2>
    <x-card-view
        model="Document"
        module="AEGIS"
    />
@endif
