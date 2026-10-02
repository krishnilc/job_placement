<?php

namespace Database\Seeders;

use App\Models\College;
use Illuminate\Database\Seeder;

class CollegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colleges = [
            ['id' => 1, 'name' => 'College of Agriculture, Fisheries & Forestry', 'code' => 'CAFF', 'status' => 1],
            ['id' => 2, 'name' => 'College of Business, Hospitality & Tourism Studies', 'code' => 'CBHTS', 'status' => 1],
            ['id' => 3, 'name' => 'College of Engineering & TVET', 'code' => 'CETVET', 'status' => 1],
            ['id' => 4, 'name' => 'College of Humanities, Education & Law', 'code' => 'CHEL', 'status' => 1],
            ['id' => 5, 'name' => 'College of Medicine, Nursing & Health Sciences', 'code' => 'CMNHS', 'status' => 1],
            ['id' => 6, 'name' => 'National Training and Productivity Centre', 'code' => 'NTPC', 'status' => 1],
            ['id' => 7, 'name' => 'Pacific Centre for Maritime Studies', 'code' => 'PCMS', 'status' => 1],
        ];

        foreach ($colleges as $college) {
            College::updateOrCreate(['id' => $college['id']], $college);
        }
    }
}
