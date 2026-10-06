<dl class="row mb-4">
    <dt class="col-sm-3">Name</dt>
    <dd class="col-sm-9">{{ $name ?: 'Not provided' }}</dd>
    <dt class="col-sm-3">Location</dt>
    <dd class="col-sm-9">{{ $location ?: 'Not provided' }}</dd>
    <dt class="col-sm-3">Website</dt>
    <dd class="col-sm-9">
        @if ($website)
            <a href="{{ $website }}" target="_blank" rel="noopener noreferrer">{{ $website }}</a>
        @else
            Not provided
        @endif
    </dd>
</dl>
