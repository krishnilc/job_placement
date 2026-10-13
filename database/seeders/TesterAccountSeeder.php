<?php

namespace Database\Seeders;

use App\Models\College;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class TesterAccountSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Tester accounts may only be seeded in local or testing environments.');
        }

        DB::transaction(function () {
            $college = College::firstOrCreate(['code' => 'TEST'], [
                'name' => 'Tester College',
                'status' => 1,
            ]);
            $password = Hash::make('test123');
            $testers = [
                'sd' => 'Systems Developer',
                'wm' => 'Webmaster',
                'wcd' => 'Web Content Developer',
                'its' => 'IT Specialist',
            ];
            $accounts = [
                'student1' => 'student',
                'student2' => 'student',
                'employer1' => 'employer',
                'employer2' => 'employer',
                'admin' => 'admin',
                'management' => 'management',
                'super_admin' => 'super_admin',
            ];

            foreach ($testers as $prefix => $tester) {
                foreach ($accounts as $account => $role) {
                    $user = User::firstOrNew(['email' => "{$prefix}.{$account}@test.example"]);
                    $user->fill([
                        'name' => "{$prefix}_{$account} ({$tester})",
                        'password' => $password,
                        'role' => $role,
                        'status' => 'active',
                        'mobile' => '7000000',
                        'designation' => $role === 'student' ? 'Full-time Student' : ucwords(str_replace('_', ' ', $role)),
                    ]);
                    $user->forceFill([
                        'email_verified_at' => now(),
                        'email_verification_required' => true,
                    ])->save();

                    if ($role === 'student') {
                        $number = (int) substr($account, -1);
                        $user->studentProfile()->updateOrCreate([], [
                            'student_id' => $prefix.'_000'.$number,
                            'date_of_birth' => $number === 1 ? '2003-01-15' : '2004-06-20',
                            'gender' => $number === 1 ? 'Male' : 'Female',
                            'marital_status' => 'Single',
                            'residential_address' => "{$number} Test Street, Suva",
                            'postal_address' => "PO Box {$number}, Suva",
                            'city' => 'Suva',
                            'country' => 'Fiji',
                            'high_school' => "{$prefix}_Test High School",
                            'high_school_graduation_year' => '2021',
                            'college_id' => $college->id,
                            'degree' => 'Bachelor of Information Technology',
                            'major' => 'Software Engineering',
                            'graduation_year' => '2027',
                            'skills' => 'PHP, Laravel, SQL',
                            'bio' => "Test student {$number} for {$tester}.",
                            'availability' => 'Available for internships',
                        ]);
                    } elseif ($role === 'employer') {
                        $name = "{$prefix}_{$account} Test Organization";
                        $organization = Organization::updateOrCreate([
                            'name_key' => Organization::nameKey($name),
                        ], [
                            'name' => $name,
                            'address' => '1 Test Business Road, Suva, Fiji',
                            'phone' => '7000000',
                            'email' => "{$prefix}.{$account}.organization@test.example",
                            'description' => "Test organization for {$tester}, {$account}.",
                        ]);
                        $user->employerProfile()->updateOrCreate([], [
                            'organization_id' => $organization->id,
                        ]);
                    }
                }
            }
        });
    }
}
