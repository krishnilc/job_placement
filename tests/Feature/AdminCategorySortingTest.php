<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\College;
use App\Models\Job;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategorySortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_count_heading_sorts_categories_in_both_directions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $jobType = JobType::factory()->create();
        $fewJobs = Category::factory()->create(['name' => 'Few Jobs']);
        $manyJobs = Category::factory()->create(['name' => 'Many Jobs']);

        Job::factory()->create([
            'user_id' => $admin->id,
            'job_type_id' => $jobType->id,
            'category_id' => $fewJobs->id,
        ]);
        foreach (range(1, 2) as $_) {
            Job::factory()->create([
                'user_id' => $admin->id,
                'job_type_id' => $jobType->id,
                'category_id' => $manyJobs->id,
            ]);
        }

        foreach (['asc', 'desc'] as $direction) {
            $expected = $direction === 'asc'
                ? [$fewJobs->id, $manyJobs->id]
                : [$manyJobs->id, $fewJobs->id];
            $this->actingAs($admin)
                ->get(route('admin.categories', ['sort' => 'jobs_count', 'direction' => $direction]))
                ->assertOk()
                ->assertViewHas('categories', fn ($categories) => $categories->pluck('id')->all() === $expected);
        }
    }

    public function test_college_heading_sorts_categories_in_both_directions(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $alphaCollege = College::create(['name' => 'Alpha College', 'code' => 'ALP', 'status' => 1]);
        $zuluCollege = College::create(['name' => 'Zulu College', 'code' => 'ZUL', 'status' => 1]);
        $alphaCategory = Category::factory()->create(['name' => 'Alpha Category', 'college_id' => $alphaCollege->id]);
        $zuluCategory = Category::factory()->create(['name' => 'Zulu Category', 'college_id' => $zuluCollege->id]);

        foreach (['asc', 'desc'] as $direction) {
            $expected = $direction === 'asc'
                ? [$alphaCategory->id, $zuluCategory->id]
                : [$zuluCategory->id, $alphaCategory->id];
            $this->actingAs($admin)
                ->get(route('admin.categories', ['sort' => 'college', 'direction' => $direction]))
                ->assertOk()
                ->assertViewHas('categories', fn ($categories) => $categories->pluck('id')->all() === $expected);
        }
    }
}
