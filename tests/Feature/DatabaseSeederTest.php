<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_migrations_and_default_seeding_do_not_create_users(): void
    {
        $this->assertDatabaseCount('users', 0);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseHas('job_types', ['name' => 'Internship']);
        $this->assertDatabaseHas('colleges', ['code' => 'CAFF']);
        $this->assertDatabaseHas('application_statuses', ['name' => 'Submitted']);
    }

    public function test_default_seeding_leaves_existing_users_unchanged(): void
    {
        $user = User::factory()->create();
        $attributes = $user->fresh()->getAttributes();

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $this->assertSame($attributes, $user->fresh()->getAttributes());
    }
}
