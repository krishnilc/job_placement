<?php

namespace Tests\Feature;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminStudentEditRequiredFieldsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, array{0: string}>
     */
    public static function adminRoleProvider(): array
    {
        return [['admin'], ['super_admin']];
    }

    #[DataProvider('adminRoleProvider')]
    public function test_student_mandatory_fields_are_marked_with_asterisk(string $role): void
    {
        $admin = User::factory()->create(['role' => $role]);
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.edit', $student->id))
            ->assertOk();

        $required = array_merge(
            ['name', 'student_id', 'email', 'mobile', 'designation'],
            StudentProfile::REQUIRED_COMPLETION_FIELDS,
        );

        foreach ($required as $field) {
            $response->assertSee('for="'.$field.'" class="mb-2">', false);
            $this->assertMatchesRegularExpression(
                '/for="'.preg_quote($field, '/').'" class="mb-2">\s*[^<]*<span class="text-danger">\*<\/span>/',
                $response->getContent(),
                $field.' should be marked as mandatory.'
            );
            $this->assertMatchesRegularExpression(
                '/<(?:input|select|textarea)\b(?=[^>]*\bname="'.preg_quote($field, '/').'"[^>]*)(?=[^>]*\brequired\b)[^>]*>/',
                $response->getContent(),
                $field.' should have the required attribute.'
            );
        }
    }

    #[DataProvider('adminRoleProvider')]
    public function test_optional_student_fields_are_not_marked_with_asterisk(string $role): void
    {
        $admin = User::factory()->create(['role' => $role]);
        $student = User::factory()->create(['role' => 'student']);

        $content = $this->actingAs($admin)
            ->get(route('admin.users.edit', $student->id))
            ->assertOk()
            ->getContent();

        foreach (['email_2', 'mobile_2', 'postal_address', 'linkedin_url', 'facebook_url'] as $field) {
            $this->assertDoesNotMatchRegularExpression(
                '/for="'.preg_quote($field, '/').'" class="mb-2">\s*[^<]*<span class="text-danger">\*<\/span>/',
                $content,
                $field.' should not be marked as mandatory.'
            );
        }
    }

    #[DataProvider('adminRoleProvider')]
    public function test_admin_cannot_save_student_without_mandatory_fields(string $role): void
    {
        $admin = User::factory()->create(['role' => $role]);
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($admin)
            ->putJson(route('admin.users.update', $student->id), [])
            ->assertOk()
            ->assertJsonPath('status', false);

        $required = array_merge(
            ['name', 'student_id', 'email', 'mobile', 'designation'],
            StudentProfile::REQUIRED_COMPLETION_FIELDS,
        );

        $response->assertJsonPath(
            'errors',
            fn (array $errors): bool => collect($required)
                ->every(fn (string $field): bool => isset($errors[$field]))
        );
    }
}
