@if($bio)
    <x-details :details="$details" />
    <hr>
    <div class="max-height">
        <h6><strong>Pen Profile</strong></h6>
        <p>{!! $bio !!}</p>
    </div>
@endif
