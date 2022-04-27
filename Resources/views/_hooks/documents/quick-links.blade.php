@if($links)
    <x-grid class="quick-links">
        @foreach($links as $link)
            @php
                if (!isset($link['class'])) {
                    $link['class'] = '';
                }
                $link['class'] .= ' shadow p-3';
                $link['style']  = 'primary';
            @endphp
            @include(
                'components.link',
                $link
            )
        @endforeach
    </x-grid>
@endif
