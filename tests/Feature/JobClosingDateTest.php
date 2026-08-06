<?php

namespace Tests\Feature;

use App\Http\Controllers\AccountController;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class JobClosingDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_creation_persists_closing_date(): void
    {
        $user = User::factory()->create(['role' => 'employer']);
        $category = Category::factory()->create();
        $jobType = JobType::factory()->create();

        $controller = new AccountController();
        $request = Request::create(route('account.saveJob'), 'POST', [
            'title' => 'Senior Laravel Developer',
            'category' => $category->id,
            'job_type' => $jobType->id,
            'vacancy' => 2,
            'location' => 'Remote',
            'description' => 'Build great products.',
            'company_name' => 'Acme Tech',
            'closing_date' => '2026-09-15',
        ]);
        $request->setUserResolver(fn () => $user);
        Auth::shouldReceive('id')->andReturn($user->id);

        $response = $controller->saveJob($request);

        $this->assertTrue($response->getData()->status);

        $job = Job::latest()->first();

        $this->assertNotNull($job);
        $this->assertSame('2026-09-15', $job->closing_date);
    }
}
