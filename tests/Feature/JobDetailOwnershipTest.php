<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobDetailOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_does_not_see_save_or_apply_actions_on_their_own_job_detail_page(): void
    {
        $owner = User::factory()->create();
        $jobType = JobType::factory()->create();
        $category = Category::factory()->create();

        $job = Job::factory()->create([
            'user_id' => $owner->id,
            'job_type_id' => $jobType->id,
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($owner)->get(route('jobDetail', $job->id));

        $response->assertOk();
        $response->assertDontSee('onclick="saveJob(');
        $response->assertDontSee('onclick="openApplyModal(');
        $response->assertDontSee('Login to Save');
        $response->assertDontSee('Login to Apply');
    }
}
