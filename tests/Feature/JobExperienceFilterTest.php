<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobExperienceFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_list_can_be_filtered_for_no_experience_and_some_experience(): void
    {
        $user = User::factory()->create(['role' => 'employer']);
        $category = Category::factory()->create(['status' => 1]);
        $jobType = JobType::factory()->create(['status' => 1]);

        Job::factory()->create([
            'title' => 'Entry Level Role',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'job_type_id' => $jobType->id,
            'experience' => '0',
            'status' => 1,
        ]);
        Job::factory()->create([
            'title' => 'Experienced Role',
            'user_id' => $user->id,
            'category_id' => $category->id,
            'job_type_id' => $jobType->id,
            'experience' => '2',
            'status' => 1,
        ]);

        $this->get(route('front.jobs', ['experience' => '0']))
            ->assertOk()
            ->assertSee('Entry Level Role')
            ->assertDontSee('Experienced Role');

        $this->get(route('front.jobs', ['experience' => '2']))
            ->assertOk()
            ->assertSee('Experienced Role')
            ->assertDontSee('Entry Level Role');
    }
}
