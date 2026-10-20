<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PopularCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_popular_categories_count_and_rank_only_open_jobs(): void
    {
        $this->travelTo(now()->startOfDay());
        $employer = User::factory()->create(['role' => 'employer']);
        $jobType = JobType::factory()->create();
        $openCategory = Category::factory()->create(['name' => 'Open Opportunities', 'status' => 1]);
        $closedCategory = Category::factory()->create(['name' => 'Closed Opportunities', 'status' => 1]);
        $inactiveCategory = Category::factory()->create(['name' => 'Hidden Category', 'status' => 0]);

        foreach ([
            [$openCategory, 1, null],
            [$openCategory, 1, today()->toDateString()],
            [$openCategory, 1, today()->addDay()->toDateString()],
            [$closedCategory, 1, today()->subDay()->toDateString()],
            [$closedCategory, 0, null],
            [$closedCategory, 0, today()->addDay()->toDateString()],
            [$closedCategory, 2, null],
            [$inactiveCategory, 1, null],
        ] as [$category, $status, $closingDate]) {
            Job::factory()->create([
                'user_id' => $employer->id,
                'job_type_id' => $jobType->id,
                'category_id' => $category->id,
                'status' => $status,
                'closing_date' => $closingDate,
            ]);
        }

        $response = $this->get(route('home'))->assertOk();
        $categories = $response->viewData('categories');

        $this->assertSame([$openCategory->id, $closedCategory->id], $categories->pluck('id')->all());
        $this->assertSame([3, 0], $categories->pluck('jobs_count')->all());
        $response->assertSee('<span> 3 </span> Available position(s)', false)
            ->assertSee('<span> 0 </span> Available position(s)', false)
            ->assertDontSee('Hidden Category');
    }
}
