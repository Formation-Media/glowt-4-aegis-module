@extends(
    'layouts.account',
    array(
        'breadcrumbs'=>array(
            'management'=>__('dictionary.management'),
            $module->getName(),
            __('aegis::phrases.feedback-list-types')
        ),
    )
)
@section('content')
    <x-card-view
        model="FeedbackListType"
        module="AEGIS"
        view="row"
    />
    {{-- <x-modal id="add-feedback-list-type" :title="['phrases.add', ['item' => __('aegis::phrases.feedback-list-type')]]" save-style="success" save-text="dictionary.add">
        <x-form name="types">
            <x-field
                label="dictionary.name"
                name="name"
                required
                type="text"
            />
        </x-form>
    </x-modal> --}}
@endsection
