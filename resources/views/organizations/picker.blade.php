<div class="mb-3" id="organizationPicker" data-search-url="{{ route('organizations.search') }}" @isset($emptyMessage) data-empty-message="{{ $emptyMessage }}" @endisset>
    <label for="organization_search" class="form-label">Search your organization<span class="text-danger">*</span></label>
    <div class="d-flex gap-2">
        <input type="search" id="organization_search" class="form-control" placeholder="Enter at least 2 characters" maxlength="255">
        <button type="button" id="organizationSearchButton" class="btn btn-outline-primary">Search</button>
    </div>
    <p id="organizationSearchMessage" class="small mt-2" role="status" aria-live="polite"></p>
    <label for="organization_id" class="form-label">Select organization<span class="text-danger">*</span></label>
    <select id="organization_id" name="organization_id" class="form-select" @required($required ?? false)>
        <option value="">Search and select an organization</option>
        @if (isset($user) && $user->employerProfile?->organization)
            <option value="{{ $user->employerProfile->organization_id }}" selected>{{ $user->company_name }}</option>
        @endif
    </select>
    <p class="text-danger" id="organization_idError"></p>
</div>
