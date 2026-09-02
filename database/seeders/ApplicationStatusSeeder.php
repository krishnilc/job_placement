<?php

namespace Database\Seeders;

use App\Models\ApplicationStatus;
use Illuminate\Database\Seeder;

class ApplicationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['id' => 1, 'name' => 'Submitted', 'category' => 'Active', 'sort_order' => 1],
            ['id' => 2, 'name' => 'Under Review', 'category' => 'Active', 'sort_order' => 2],
            ['id' => 3, 'name' => 'Shortlisted', 'category' => 'Active', 'sort_order' => 3],
            ['id' => 4, 'name' => 'Interview Scheduled', 'category' => 'Active', 'sort_order' => 4],
            ['id' => 5, 'name' => 'Interview Completed', 'category' => 'Active', 'sort_order' => 5],
            ['id' => 6, 'name' => 'Accepted', 'category' => 'Successful', 'sort_order' => 6],
            ['id' => 7, 'name' => 'Placed', 'category' => 'Successful', 'sort_order' => 7],
            ['id' => 8, 'name' => 'Rejected', 'category' => 'Unsuccessful', 'sort_order' => 8],
            ['id' => 9, 'name' => 'Withdrawn', 'category' => 'Withdrawn', 'sort_order' => 9],
        ];

        foreach ($statuses as $status) {
            ApplicationStatus::updateOrCreate(['id' => $status['id']], $status);
        }
    }
}