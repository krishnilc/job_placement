@props(['label', 'value', 'color' => 'success', 'icon' => 'fa-briefcase'])

<div class="col-md-6 col-lg-3 mb-3">
    <div class="card border-0 shadow h-100">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">{{ $label }}</p>
                    <h2 class="text-{{ $color }} mb-0">{{ $value }}</h2>
                </div>
                <div class="text-{{ $color }}" style="font-size: 2rem;">
                    <i class="fa {{ $icon }}" aria-hidden="true"></i>
                </div>
            </div>
        </div>
    </div>
</div>
