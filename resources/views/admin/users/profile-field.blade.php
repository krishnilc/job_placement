@php
    $fieldValue = $value ?: 'Not provided';
    $columnClass = !empty($wide) ? 'col-12' : 'col-md-6';
@endphp
<div class="{{ $columnClass }}">
    <div class="admin-profile-field">
        <span class="admin-profile-label">{{ $label }}</span>
        @if (!empty($link) && $value)
            <a href="{{ $value }}" target="_blank" rel="noopener noreferrer" class="admin-profile-value admin-profile-link">{{ $value }}</a>
        @else
            <span class="admin-profile-value {{ !$value ? 'admin-profile-empty' : '' }}">{{ $fieldValue }}</span>
        @endif
    </div>
</div>
