<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\EmployerProfile;
use App\Models\Job;
use App\Models\JobType;
use App\Models\Organization;
use App\Models\OrganizationRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    private function registration(array $changes = []): array
    {
        return array_merge([
            'role' => 'employer', 'name' => 'Employer Contact',
            'email' => 'contact@example.com', 'mobile' => '1234567',
            'designation' => 'HR Manager', 'password' => 'secret123',
            'confirm_password' => 'secret123',
        ], $changes);
    }

    private function requestOrganization(): OrganizationRequest
    {
        $this->post(route('account.processRegistration'), $this->registration([
            'organization_mode' => 'request', 'company_name' => 'New Organization',
            'company_address' => 'Suva',
        ]))->assertOk()->assertJson(['status' => true]);

        return OrganizationRequest::firstOrFail();
    }

    public function test_registration_page_has_organization_search_and_request_option(): void
    {
        $this->get(route('account.registration'))->assertOk()
            ->assertSee('Search your organization')
            ->assertSee("Can't find your organization? Request a new organization.", false)
            ->assertSee('assets/js/organization-picker.js', false);
    }

    public function test_public_search_returns_only_organization_ids_and_names(): void
    {
        $organization = Organization::create(['name' => 'Example Organization', 'address' => 'Private office']);
        Organization::create(['name' => 'Other Company', 'address' => 'Lautoka']);
        $this->getJson(route('organizations.search', ['search' => 'EXAMPLE']))
            ->assertOk()->assertExactJson(['organizations' => [['id' => $organization->id, 'name' => 'Example Organization']]]);
        $this->getJson(route('organizations.search', ['search' => 'missing']))->assertOk()->assertExactJson(['organizations' => []]);
        $this->getJson(route('organizations.search', ['search' => 'a']))->assertUnprocessable();
    }

    public function test_multiple_contacts_register_for_the_same_organization(): void
    {
        $organization = Organization::create(['name' => 'Example Organization', 'address' => 'Suva']);
        foreach (['one@example.com', 'two@example.com'] as $email) {
            $this->post(route('account.processRegistration'), $this->registration([
                'email' => $email, 'organization_mode' => 'existing', 'organization_id' => $organization->id,
            ]))->assertJson(['status' => true]);
        }
        $this->assertSame(2, $organization->employerProfiles()->count());
        $this->assertDatabaseCount('organizations', 1);
        $this->assertDatabaseCount('organization_requests', 0);
        $this->assertSame(2, User::where('role', 'employer')->where('status', 'pending')->count());
    }

    public function test_new_organization_request_creates_pending_contact_not_organization(): void
    {
        $pending = $this->requestOrganization();
        $this->assertSame('pending', $pending->status);
        $this->assertSame('pending', $pending->user->status);
        $this->assertNull($pending->user->employerProfile->organization_id);
        $this->assertDatabaseCount('organizations', 0);
        $this->post(route('account.authenticate'), ['email' => 'contact@example.com', 'password' => 'secret123'])
            ->assertRedirect(route('account.login'));
        $this->assertGuest();
    }

    public function test_registration_rejects_invalid_selection_and_duplicate_normalized_request(): void
    {
        Organization::create(['name' => 'Example Organization', 'address' => 'Suva']);
        $this->post(route('account.processRegistration'), $this->registration([
            'organization_mode' => 'existing', 'organization_id' => 99999,
        ]))->assertJson(['status' => false])->assertJsonStructure(['errors' => ['organization_id']]);
        $this->post(route('account.processRegistration'), $this->registration([
            'organization_mode' => 'request', 'company_name' => '  EXAMPLE   ORGANIZATION  ', 'company_address' => 'Suva',
        ]))->assertJson(['status' => false])->assertJsonStructure(['errors' => ['company_name']]);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_admin_and_super_admin_can_search_before_approving_and_contact_approval_is_separate(): void
    {
        $pending = $this->requestOrganization();
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.organizations.index'))->assertOk()->assertSee('Pending Organization Requests');
        $this->get(route('admin.organizations.review', $pending))->assertOk()
            ->assertSee('Search existing organizations before approving')->assertSee('New Organization');
        $this->patch(route('admin.users.employers.status', $pending->user_id), ['status' => 'active'])
            ->assertSessionHasErrors('status');
        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->patch(route('admin.organizations.resolve', $pending), ['decision' => 'approve'])
            ->assertRedirect(route('admin.organizations.index'));
        $pending->refresh();
        $this->assertSame('approved', $pending->status);
        $this->assertSame('pending', $pending->user->status);
        $this->assertSame($pending->organization_id, $pending->user->employerProfile->organization_id);
        $this->patchJson(route('admin.users.employers.status', $pending->user_id), ['status' => 'active'])
            ->assertOk()->assertJson(['status' => true]);
        $this->assertSame('active', $pending->user->fresh()->status);
    }

    public function test_admin_can_link_request_to_existing_organization_instead_of_creating_duplicate(): void
    {
        $pending = $this->requestOrganization();
        $organization = Organization::create(['name' => 'Existing Organization', 'address' => 'Nadi']);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.organizations.review', [$pending, 'search' => 'Existing']))
            ->assertOk()->assertSee('Existing Organization')->assertSee('Link to this organization');
        $this->patch(route('admin.organizations.resolve', $pending), [
            'decision' => 'link', 'organization_id' => $organization->id,
        ])->assertRedirect();
        $this->assertDatabaseCount('organizations', 1);
        $this->assertSame($organization->id, $pending->user->fresh()->employerProfile->organization_id);
        $this->assertSame($admin->id, $pending->fresh()->reviewed_by);
        $this->patchJson(route('admin.organizations.resolve', $pending), ['decision' => 'approve'])
            ->assertUnprocessable()->assertJsonValidationErrors('decision');
    }

    public function test_approval_refuses_duplicate_name_and_rejection_requires_reason(): void
    {
        $pending = $this->requestOrganization();
        Organization::create(['name' => 'new organization', 'address' => 'Nadi']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->patchJson(route('admin.organizations.resolve', $pending), ['decision' => 'approve'])
            ->assertUnprocessable()->assertJsonValidationErrors('decision');
        $this->patchJson(route('admin.organizations.resolve', $pending), ['decision' => 'reject'])
            ->assertUnprocessable()->assertJsonValidationErrors('review_notes');
        $this->patch(route('admin.organizations.resolve', $pending), [
            'decision' => 'reject', 'review_notes' => 'Unable to verify this organization.',
        ])->assertRedirect();
        $this->assertSame('rejected', $pending->fresh()->status);
        $this->assertNull($pending->user->fresh()->employerProfile->organization_id);
        $this->patchJson(route('admin.users.employers.status', $pending->user_id), ['status' => 'active'])
            ->assertUnprocessable();
    }

    public function test_organization_management_is_not_available_to_employers_or_students(): void
    {
        $organization = Organization::create(['name' => 'Example Company', 'address' => 'Suva']);
        $pending = $this->requestOrganization();
        foreach (['employer', 'student'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route('admin.organizations.index'))->assertRedirect();
            $this->patch(route('admin.organizations.resolve', $pending), ['decision' => 'approve'])->assertRedirect();
            $this->put(route('admin.organizations.update', $organization), ['name' => 'Changed', 'address' => 'Nadi'])->assertRedirect();
        }
        $this->assertSame('pending', $pending->fresh()->status);
        $this->assertSame('Example Company', $organization->fresh()->name);
    }

    public function test_shared_details_update_for_all_contacts_and_contacts_cannot_edit_them(): void
    {
        $organization = Organization::create(['name' => 'Shared Company', 'address' => 'Suva']);
        $contacts = [];
        for ($i = 0; $i < 2; $i++) {
            $contact = User::factory()->create(['role' => 'employer', 'status' => 'active']);
            $contact->employerProfile()->create(['organization_id' => $organization->id]);
            $contacts[] = $contact;
        }
        $this->actingAs(User::factory()->create(['role' => 'admin']))->put(route('admin.organizations.update', $organization), [
            'name' => 'Updated Company', 'address' => 'Nadi', 'website_url' => 'https://example.com',
        ])->assertRedirect();
        foreach ($contacts as $contact) {
            $this->assertSame('Updated Company', $contact->fresh()->company_name);
            $this->assertSame('Nadi', $contact->fresh()->company_address);
        }
        $contact = $contacts[0];
        $this->actingAs($contact)->get(route('employer.account.editProfile'))->assertOk()->assertSee('fieldset disabled', false);
        $personal = ['name' => 'Contact Updated', 'email' => $contact->email, 'mobile' => '1234567', 'designation' => 'Manager'];
        $this->put(route('account.updateProfile'), [...$personal, 'company_name' => 'Hijacked'])
            ->assertJson(['status' => false])->assertJsonStructure(['errors' => ['company_name']]);
        $this->put(route('account.updateProfile'), $personal)->assertJson(['status' => true]);
        $this->assertSame('Contact Updated', $contact->fresh()->name);
        $this->assertSame('Updated Company', $organization->fresh()->name);
        $this->putJson(route('admin.users.update', $contacts[1]), [...$personal, 'organization_id' => $organization->id])
            ->assertForbidden();
    }

    public function test_admin_contact_creation_search_sort_and_organization_filter(): void
    {
        $alpha = Organization::create(['name' => 'Alpha Company', 'address' => 'Suva']);
        $zulu = Organization::create(['name' => 'Zulu Company', 'address' => 'Nadi']);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach ([$alpha, $zulu] as $organization) {
            $this->post(route('admin.users.employers.store'), $this->registration([
                'email' => $organization->id.'@example.com', 'organization_id' => $organization->id,
            ]))->assertJson(['status' => true]);
        }
        $this->get(route('admin.users.employers', ['sort' => 'company_name', 'direction' => 'desc']))
            ->assertOk()->assertViewHas('users', fn ($users) => $users->first()->company_name === 'Zulu Company');
        $this->get(route('admin.users.employers', ['search' => 'Alpha']))
            ->assertOk()->assertViewHas('users', fn ($users) => $users->count() === 1);
        $this->get(route('admin.users.employers', ['organization_id' => $zulu->id]))
            ->assertOk()->assertViewHas('users', fn ($users) => $users->count() === 1 && $users->first()->company_name === 'Zulu Company');
    }

    public function test_admin_can_edit_pending_contact_but_cannot_bypass_request_review(): void
    {
        $pending = $this->requestOrganization();
        $organization = Organization::create(['name' => 'Existing Company', 'address' => 'Suva']);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $data = [
            'name' => 'Updated Contact', 'email' => $pending->user->email,
            'mobile' => '1234567', 'designation' => 'Manager', 'status' => 'pending',
        ];
        $this->get(route('admin.users.edit', $pending->user_id))->assertOk()->assertSee('Review this contact');
        $this->put(route('admin.users.update', $pending->user_id), $data)->assertJson(['status' => true]);
        $this->assertSame('Updated Contact', $pending->user->fresh()->name);
        $this->put(route('admin.users.update', $pending->user_id), [
            ...$data, 'status' => 'active', 'organization_id' => $organization->id,
        ])->assertJson(['status' => false])->assertJsonStructure(['errors' => ['status']]);
        $this->assertSame('pending', $pending->user->fresh()->status);
    }

    public function test_admin_can_close_a_request_after_its_contact_is_deleted(): void
    {
        $pending = $this->requestOrganization();
        $pending->user->delete();
        $this->actingAs(User::factory()->create(['role' => 'super_admin']))
            ->patch(route('admin.organizations.resolve', $pending), [
                'decision' => 'reject', 'review_notes' => 'Contact account was deleted.',
            ])->assertRedirect();
        $this->assertNull($pending->fresh()->user_id);
        $this->assertSame('rejected', $pending->fresh()->status);
    }

    public function test_admin_organization_creation_and_edits_prevent_duplicate_names(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get(route('admin.organizations.create'))->assertOk();
        $this->post(route('admin.organizations.store'), ['name' => 'Example Company', 'address' => 'Suva'])->assertRedirect();
        $this->postJson(route('admin.organizations.store'), ['name' => 'EXAMPLE   COMPANY', 'address' => 'Nadi'])
            ->assertUnprocessable()->assertJsonValidationErrors('name');
        $other = Organization::create(['name' => 'Other Company', 'address' => 'Nadi']);
        $this->get(route('admin.organizations.edit', $other))->assertOk()->assertSee('Edit Organization');
        $this->putJson(route('admin.organizations.update', $other), ['name' => 'example company', 'address' => 'Nadi'])
            ->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->assertDatabaseCount('organizations', 2);
        $this->assertSame('Other Company', $other->fresh()->name);
    }

    public function test_contact_deletion_and_shared_organization_do_not_transfer_job_access(): void
    {
        $organization = Organization::create(['name' => 'Shared Company', 'address' => 'Suva']);
        $owner = User::factory()->create(['role' => 'employer', 'status' => 'active']);
        $other = User::factory()->create(['role' => 'employer', 'status' => 'active']);
        foreach ([$owner, $other] as $contact) {
            $contact->employerProfile()->create(['organization_id' => $organization->id]);
        }
        $category = Category::factory()->create();
        $type = JobType::create(['name' => 'Full Time', 'status' => 1]);
        $this->actingAs($owner)->post(route('account.saveJob'), [
            'title' => 'Example position', 'category' => $category->id, 'job_type' => $type->id,
            'vacancy' => 1, 'location' => 'Suva', 'description' => 'Position details',
            'experience' => 0, 'company_name' => 'Shared Company',
        ])->assertJson(['status' => true]);
        $job = Job::firstOrFail();
        $this->assertSame($organization->id, $job->organization_id);
        $this->assertSame($owner->id, $job->user_id);
        $this->actingAs($other)->get(route('account.editJob', $job->id))->assertNotFound();
        $other->delete();
        $this->assertDatabaseHas('organizations', ['id' => $organization->id]);
        $this->assertSame($organization->id, $owner->fresh()->employerProfile->organization_id);
    }

    public function test_backfill_groups_names_and_preserves_existing_jobs_and_contact_details(): void
    {
        $migration = require database_path('migrations/2026_10_05_000000_create_organizations_and_requests.php');
        $migration->down();
        $first = User::factory()->create(['role' => 'employer']);
        $second = User::factory()->create(['role' => 'employer']);
        EmployerProfile::create(['user_id' => $first->id, 'company_name' => ' Example   Company ', 'company_address' => 'Suva']);
        EmployerProfile::create(['user_id' => $second->id, 'company_name' => 'example company', 'company_address' => 'Nadi', 'website_url' => 'https://example.com']);
        $job = Job::factory()->create([
            'user_id' => $second->id, 'company_name' => 'Historical Company Name',
            'category_id' => Category::factory()->create()->id,
            'job_type_id' => JobType::create(['name' => 'Full Time', 'status' => 1])->id,
        ]);
        $migration->up();
        $this->assertDatabaseCount('organizations', 1);
        $organization = Organization::firstOrFail();
        $this->assertSame('Suva', $organization->address);
        $this->assertSame('https://example.com', $organization->website_url);
        $this->assertSame($organization->id, $first->fresh()->employerProfile->organization_id);
        $this->assertSame($organization->id, $second->fresh()->employerProfile->organization_id);
        $this->assertSame($organization->id, $job->fresh()->organization_id);
        $this->assertSame('Historical Company Name', $job->fresh()->company_name);
        $this->assertSame($second->id, $job->fresh()->user_id);
        $this->assertDatabaseHas('employer_profiles', ['user_id' => $second->id, 'company_address' => 'Nadi']);
    }
}
