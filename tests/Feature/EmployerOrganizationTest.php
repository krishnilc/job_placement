<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployerOrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_employer_job_forms_display_read_only_organization_details(): void
    {
        $organization = Organization::create([
            'name' => 'Linked Organization', 'address' => 'Suva Office',
            'website_url' => 'https://example.com',
        ]);
        $employer = User::factory()->create(['role' => 'employer', 'status' => 'active']);
        $employer->employerProfile()->create(['organization_id' => $organization->id]);
        $job = Job::factory()->create([
            'user_id' => $employer->id, 'organization_id' => $organization->id,
            'category_id' => Category::factory()->create()->id,
            'job_type_id' => JobType::create(['name' => 'Full Time', 'status' => 1])->id,
        ]);
        $this->actingAs($employer);
        foreach ([route('account.createJob'), route('account.editJob', $job->id)] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('Organization Details')
                ->assertSee('<dl class="row mb-4">', false)
                ->assertSee('Linked Organization')->assertSee('Suva Office')
                ->assertSee('https://example.com')
                ->assertSee(route('employer.organization'))
                ->assertDontSee('name="company_name"', false)
                ->assertDontSee('name="company_location"', false)
                ->assertDontSee('name="company_website"', false);
        }
        $organization->update(['address' => null, 'website_url' => null]);
        $this->actingAs($employer->fresh())->get(route('account.createJob'))
            ->assertOk()->assertSee('Not provided')->assertDontSee('href="#" target="_blank"', false);
    }

    public function test_employer_can_view_only_their_linked_organization(): void
    {
        $organization = Organization::create([
            'name' => 'My Company',
            'address' => 'Suva Office',
            'phone' => '331 4411',
            'postal_address' => 'PO Box 123',
            'website_url' => 'https://example.com',
            'linkedin_url' => 'https://www.linkedin.com/company/example',
            'facebook_url' => 'https://www.facebook.com/example',
            'description' => 'Our organization description.',
        ]);
        $other = Organization::create(['name' => 'Other Private Company', 'phone' => '9999999']);
        $employer = User::factory()->create(['role' => 'employer', 'status' => 'active']);
        $employer->employerProfile()->create(['organization_id' => $organization->id]);

        $this->actingAs($employer)
            ->get(route('employer.organization', ['organization_id' => $other->id]))
            ->assertOk()
            ->assertViewHas('organization', fn ($value) => $value->id === $organization->id)
            ->assertSee('My Organization')
            ->assertSee('Organization details are managed by an administrator.')
            ->assertDontSee('Other Private Company')
            ->assertDontSee('9999999')
            ->assertDontSee('Edit details');
        $response = $this->get(route('employer.organization'));
        foreach ($organization->only(['name', 'address', 'phone', 'postal_address', 'website_url', 'linkedin_url', 'facebook_url', 'description']) as $value) {
            $response->assertSee($value);
        }
        $this->get(route('employer.dashboard'))->assertOk()
            ->assertSee(route('employer.organization'))->assertSee('My Organization');
        $this->get(route('employer.account.profile'))->assertOk()
            ->assertSee(route('employer.organization'));
    }

    public function test_missing_organization_is_explained_and_optional_fields_have_placeholders(): void
    {
        $employer = User::factory()->create(['role' => 'employer']);
        $this->actingAs($employer)->get(route('employer.organization'))->assertOk()
            ->assertSee('No organization is linked to your account yet.');
        $employer->employerProfile()->create();
        $this->get(route('employer.organization'))->assertOk()
            ->assertSee('No organization is linked to your account yet.');
        $organization = Organization::create(['name' => 'Minimal Organization']);
        $employer->employerProfile()->update(['organization_id' => $organization->id]);
        $this->actingAs($employer->fresh())->get(route('employer.organization'))->assertOk()
            ->assertSee('Minimal Organization')->assertSee('Not provided');
    }

    public function test_guests_and_other_roles_cannot_view_employer_organization_page(): void
    {
        $this->get(route('employer.organization'))->assertRedirect();
        $this->actingAs(User::factory()->create(['role' => 'student']))
            ->get(route('employer.organization'))->assertRedirect();
        foreach (['admin', 'super_admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route('employer.organization'))->assertForbidden();
        }
    }
}
