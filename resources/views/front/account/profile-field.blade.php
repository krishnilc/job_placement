@php
    $fieldValue = $value ?: 'Not provided';
    $columnClass = !empty($wide) ? 'col-12' : 'col-md-6';
@endphp
<div class="{{ $columnClass }}">
    <div class="profile-field">
        <span class="profile-field-icon"><i class="fa fa-{{ $icon }}" aria-hidden="true"></i></span>
        <div class="profile-field-content">
            <span class="profile-field-label">{{ $label }}</span>
            @if (!empty($link) && $value)
                <a href="{{ $value }}" target="_blank" rel="noopener noreferrer" class="profile-field-value profile-link">{{ $value }}</a>
            @else
                <span class="profile-field-value {{ !$value ? 'profile-empty' : '' }}">{{ $fieldValue }}</span>
            @endif
        </div>
    </div>
</div>
