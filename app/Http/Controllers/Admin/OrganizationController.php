<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationRequest;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'sort' => ['nullable', Rule::in(['name', 'address', 'phone', 'employer_profiles_count'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $sort = $request->query('sort') ?: 'name';
        $direction = $request->query('direction') ?: 'asc';
        $search = trim((string) $request->query('search', ''));
        $organizations = Organization::withCount(['employerProfiles', 'jobs'])
            ->when($search !== '', fn ($query) => $query->whereRaw('LOWER(name) LIKE ?', ['%'.Organization::normalizeName($search).'%']))
            ->orderBy($sort, $direction)->orderBy('id')->paginate(15)->withQueryString();
        $requests = OrganizationRequest::with(['user', 'organization', 'reviewer'])
            ->where('status', 'pending')->orderBy('created_at')->paginate(15, ['*'], 'requests_page')->withQueryString();

        return view('admin.organizations.index', compact('organizations', 'requests', 'search', 'sort', 'direction'));
    }

    public function create()
    {
        return view('admin.organizations.form', ['organization' => new Organization]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedOrganization($request);
        $organization = Organization::createOrFirst(['name_key' => Organization::nameKey($data['name'])], $data);
        if (! $organization->wasRecentlyCreated) {
            throw ValidationException::withMessages(['name' => 'This organization already exists. Search existing records before adding a new one.']);
        }

        return redirect()->route('admin.organizations.index')->with('success', 'Organization created successfully.');
    }

    public function show(Organization $organization)
    {
        $organization->loadCount(['employerProfiles', 'jobs']);

        return view('admin.organizations.show', compact('organization'));
    }

    public function edit(Organization $organization)
    {
        return view('admin.organizations.form', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        $data = $this->validatedOrganization($request);
        if (Organization::where('name_key', Organization::nameKey($data['name']))->where('id', '!=', $organization->id)->exists()) {
            throw ValidationException::withMessages(['name' => 'This organization name already exists.']);
        }
        try {
            $organization->update($data);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['name' => 'This organization name already exists.']);
        }

        return redirect()->route('admin.organizations.index')->with('success', 'Organization updated for all linked contacts.');
    }

    public function destroy(Organization $organization)
    {
        $organization->loadCount(['employerProfiles', 'jobs']);
        if ($organization->employer_profiles_count > 0 || $organization->jobs_count > 0) {
            return redirect()->route('admin.organizations.index')
                ->with('error', 'Cannot delete this organization: it still has linked employees or job postings.');
        }

        $organization->delete();

        return redirect()->route('admin.organizations.index')->with('success', 'Organization deleted successfully.');
    }

    private function validatedOrganization(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/\S/u'],
            'address' => ['required', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'postal_address' => ['nullable', 'string', 'max:1000'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
        ]);
    }

    public function review(Request $request, OrganizationRequest $organizationRequest)
    {
        $search = trim((string) $request->query('search', $organizationRequest->name));
        $organizations = Organization::when($search !== '', fn ($query) => $query->whereRaw('LOWER(name) LIKE ?', ['%'.Organization::normalizeName($search).'%']))
            ->orderBy('name')->paginate(15)->withQueryString();
        $organizationRequest->load('user', 'organization', 'reviewer');

        return view('admin.organizations.review', compact('organizationRequest', 'organizations', 'search'));
    }

    public function resolve(Request $request, OrganizationRequest $organizationRequest)
    {
        $data = $request->validate([
            'decision' => ['required', 'in:approve,link,reject'],
            'organization_id' => ['required_if:decision,link', 'nullable', 'integer', 'exists:organizations,id'],
            'review_notes' => ['required_if:decision,reject', 'nullable', 'string', 'max:2000'],
        ]);
        DB::transaction(function () use ($request, $organizationRequest, $data) {
            $pending = OrganizationRequest::lockForUpdate()->findOrFail($organizationRequest->id);
            if ($pending->status !== 'pending') {
                throw ValidationException::withMessages(['decision' => 'This request has already been reviewed.']);
            }
            $user = User::query()->whereKey($pending->user_id)->lockForUpdate()->first();
            if ($data['decision'] !== 'reject' && (! $user || $user->role !== 'employer')) {
                throw ValidationException::withMessages(['decision' => 'The requesting employer account no longer exists.']);
            }
            $organization = null;
            if ($data['decision'] === 'approve') {
                $organization = Organization::createOrFirst(
                    ['name_key' => Organization::nameKey($pending->name)],
                    $pending->only(['name', 'address', 'phone', 'website_url', 'description'])
                );
                if (! $organization->wasRecentlyCreated) {
                    throw ValidationException::withMessages(['decision' => 'An organization with this name already exists. Link the request to the existing record instead.']);
                }
            } elseif ($data['decision'] === 'link') {
                $organization = Organization::findOrFail($data['organization_id']);
            }
            if ($organization) {
                $user->employerProfile()->updateOrCreate(['user_id' => $user->id], ['organization_id' => $organization->id]);
            }
            $pending->update([
                'status' => $organization ? 'approved' : 'rejected',
                'organization_id' => $organization?->id,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_notes' => $data['review_notes'] ?? null,
            ]);
        });

        return redirect()->route('admin.organizations.index')->with('success', 'Organization request reviewed. Contact account approval remains a separate step.');
    }
}
