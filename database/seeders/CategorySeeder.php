<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\College;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoriesByCollege = [
            'CAFF' => [
                'Agriculture',
                'Fisheries',
                'Forestry',
                'Animal Science',
                'Veterinary',
                'Aquaculture',
                'Dairy',
                'Poultry',
                // 'Industry Attachment (IA) - CAFF',
            ],
            'CBHTS' => [
                'Accounting',
                'Professional Accounting',
                'Banking',
                'Finance',
                'Economics',
                'Border Management',
                'Marketing',
                'Human Resource',
                'Management',
                'Office Administration',
                'Customs',
                'Housekeeping and Accommodation',
                'Baking and Patisserie',
                'Front Office Operations',
                'Cookery',
                'Hospitality and Hotel Management',
                'Restaurant Operations',
                'Culinary Arts',
                'Food and Beverage Services',
                'Library and Information Systems',
                'Contact Centre Operations',
                // 'Industry Attachment (IA) - CBHTS',
            ],
            'CETVET' => [
                'Information Technology',
                'Mechanical Engineering',
                'Manufacturing Engineering',
                'Fitting and Machining',
                'Welding and Fabrication',
                'Plant Maintenance',
                'Renewable and Sustainable Engineering',
                'Agricultural Engineering',
                'Refrigeration and Air-Conditioning',
                'Automotive Mechanic',
                'Automotive Panel Beating',
                'Automotive Electrical and Electronics',
                'Industrial Lab Technology',
                'Food Technology',
                'Environmental',
                'Civil Engineering',
                'Urban and Regional Planning',
                'Land Surveying',
                'Quantity Surveying',
                'Architectural Drafting',
                'Geology, Mining and Quarrying',
                'Construction and Carpentry',
                'Joinery and Cabinet Making',
                'Plumbing and Sheetmetal',
                'Electronics Technician',
                'Electrician',
                'Telecommunication and Networking Technicians',
                'Instrumentation and Control Technicians',
                'Electrical Engineers',
                // 'Industry Attachment (IA) - CETVET',
            ],
            'CHEL' => [
                'Counsellors',
                'Lawyers',
                'Journalism',
                'Film and Television Professionals',
                'Graphic Artist and Designers',
                'Teachers',
                // 'Industry Attachment (IA) - CHEL',
            ],
            'CMNHS' => [
                'Doctors',
                'Nurses',
                'Public Health Professionals',
                'Dietician',
                'Dentist and Dental Surgeons',
                'Physiotherapists',
                'Radiologists',
                'Pharmacists',
                'Medical Lab Technicians',
                // 'Industry Attachment (IA) - CMNHS',
            ],
        ];

        foreach ($categoriesByCollege as $collegeCode => $categoryNames) {
            $college = College::where('code', $collegeCode)->first();

            if (! $college) {
                continue;
            }

            foreach ($categoryNames as $name) {
                Category::updateOrCreate(
                    ['name' => $name, 'college_id' => $college->id],
                    ['status' => 1]
                );
            }
        }
    }
}
