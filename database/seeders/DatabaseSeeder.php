<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //  User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Category::factory(5)->create();
        // JobType::factory(5)->create();

        $jobTypes = [
            'Full Time',
            'Part Time',
            'Contract',
            'Remote',
            'Freelance',
            'Attachment',
        ];

        foreach ($jobTypes as $type) {
            JobType::firstOrCreate([
                'name' => $type,
            ], [
                'status' => 1,
            ]);
        }

        $categories = [
            'Administration & Office Support',
            'Agriculture & Environment',
            'Accounting, Banking, & Finance',
            'Information Technology (IT) & Computing',
            'Engineering & Technical Fields',
            'Health & Medical Services',
            'Education & Training',
            'Hospitality & Tourism',
            'Creative Arts, Media & Design',
            'Trades & Skilled Services',
            'Science & Research',
            'Government, Legal & Community Services',
            'Student & Graduate Opportunities',
            'Sales, Marketing & Customer Service',
            'Logistics, Transport & Supply Chain',
            'Executive & Management Roles',
            'Other Opportunities',

        ];

        foreach ($categories as $category) {
            Category::firstOrCreate([
                'name' => $category,
            ], [
                'status' => 1,
            ]);
        }

        //Job::factory(20)->create();

    }
}
