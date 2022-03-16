<x-details :details="$details" />
@if($bio)
    <hr>
    <div class="max-height">
        <h6><strong>Pen Profile</strong></h6>
        <p>{!! $bio !!}</p>
    </div>
@endif
