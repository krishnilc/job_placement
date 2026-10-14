<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UserManualDownloadTest extends TestCase
{
    use RefreshDatabase;

    public static function roles(): array
    {
        return [
            'guest' => [null],
            'student' => ['student'],
            'employer' => ['employer'],
            'admin' => ['admin'],
            'management' => ['management'],
            'super admin' => ['super_admin'],
        ];
    }

    #[DataProvider('roles')]
    public function test_manual_link_and_legacy_redirect_are_available_to_every_role(?string $role): void
    {
        if ($role !== null) {
            $this->actingAs(User::factory()->create(['role' => $role, 'status' => 'active']));
        }

        $this->get('/')
            ->assertOk()
            ->assertSee(route('userManual.index'), false)
            ->assertSee('User Manuals');

        $this->get(route('userManual.download'))->assertRedirect(route('userManual.index'));
    }

    #[DataProvider('roles')]
    public function test_help_page_shows_general_and_only_the_signed_in_role_manual(?string $role): void
    {
        if ($role !== null) {
            $this->actingAs(User::factory()->create(['role' => $role, 'status' => 'active']));
        }

        $manuals = require base_path('docs/manuals.php');
        $response = $this->get(route('userManual.index'))->assertOk();
        $response->assertSee('What this guide covers')->assertDontSee('Editable Word');
        if ($role === null) {
            $response->assertSee('Sign in')->assertSee('col-lg-10 col-xl-8', false);
        } else {
            $response->assertSee('col-lg-6', false)->assertDontSee('manual-login-notice');
        }
        foreach ($manuals as $key => $manual) {
            if ($key === 'general' || $key === $role) {
                $response->assertSee($manual['title']);
                $response->assertSee(route('userManual.file', ['manual' => $key, 'format' => 'pdf']), false);
            } else {
                $response->assertDontSee('<h2 class="h4">'.$manual['title'].'</h2>', false);
            }
            $response->assertDontSee(route('userManual.file', ['manual' => $key, 'format' => 'docx']), false);
        }
    }

    #[DataProvider('roles')]
    public function test_pdf_access_is_enforced_and_word_downloads_are_unavailable(?string $role): void
    {
        if ($role !== null) {
            $this->actingAs(User::factory()->create(['role' => $role, 'status' => 'active']));
        }

        $manuals = require base_path('docs/manuals.php');
        foreach ($manuals as $key => $manual) {
            foreach (['pdf', 'docx'] as $format) {
                $response = $this->get(route('userManual.file', ['manual' => $key, 'format' => $format]));
                if ($format === 'docx') {
                    $response->assertNotFound();

                    continue;
                }
                if ($key !== 'general' && $key !== $role) {
                    $response->assertForbidden();

                    continue;
                }

                $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
                $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
                $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
                $file = $response->baseResponse->getFile();
                $this->assertSame(
                    realpath(base_path('docs/manuals/FNU-Job-Placement-'.$key.'-Manual.'.$format)),
                    $file->getRealPath()
                );
                $this->assertGreaterThan(1000, $file->getSize());
                $this->assertStringStartsWith('inline;', $response->headers->get('Content-Disposition'));
                $this->assertSame('%PDF-', file_get_contents($file->getPathname(), false, null, 0, 5));
                $this->get(route('userManual.file', ['manual' => $key, 'format' => 'pdf', 'download' => 1]))
                    ->assertOk()->assertDownload('FNU-Job-Placement-'.$key.'-Manual.pdf');
            }
        }
    }

    public function test_unknown_manuals_formats_and_path_traversal_are_rejected(): void
    {
        foreach ([
            ['manual' => 'unknown', 'format' => 'pdf'],
            ['manual' => 'general', 'format' => 'html'],
            ['manual' => '..', 'format' => 'pdf'],
        ] as $parameters) {
            $this->get(route('userManual.file', $parameters))->assertNotFound();
        }
    }

    public function test_alumni_receive_the_student_manual(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'student', 'designation' => 'Alumni', 'status' => 'active',
        ]));

        $this->get(route('userManual.index'))->assertOk()->assertSee('Student and Alumni User Manual');
        $this->get(route('userManual.file', ['manual' => 'student', 'format' => 'pdf']))->assertOk();
    }

    public function test_generated_word_documents_contain_all_source_sections_and_valid_xml(): void
    {
        $manuals = require base_path('docs/manuals.php');
        foreach ($manuals as $key => $manual) {
            $archive = new \PharData(base_path('docs/manuals/FNU-Job-Placement-'.$key.'-Manual.docx'));
            foreach (['[Content_Types].xml', '_rels/.rels', 'word/document.xml', 'word/styles.xml', 'word/_rels/document.xml.rels'] as $part) {
                $xml = new \DOMDocument;
                $this->assertTrue($xml->loadXML($archive[$part]->getContent()), $key.' '.$part);
            }
            $xml = new \DOMDocument;
            $xml->loadXML($archive['word/document.xml']->getContent());
            $text = $xml->textContent;
            $this->assertStringContainsString($manual['title'], $text);
            foreach ($manual['sections'] as $heading => $paragraphs) {
                $this->assertStringContainsString($heading, $text);
                foreach ($paragraphs as $paragraph) {
                    $this->assertStringContainsString($paragraph, $text);
                }
            }
        }
    }

    public function test_role_sidebars_link_to_the_help_page(): void
    {
        foreach (['admin', 'student', 'employer'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role, 'status' => 'active']));
            $html = view($role.'.sidebar')->render();
            $this->assertStringContainsString(route('userManual.index'), $html);
            $this->assertStringContainsString('User Manuals', $html);
        }
    }

    public function test_generated_pdfs_have_a_cover_and_one_page_per_section_without_blank_pages(): void
    {
        $manuals = require base_path('docs/manuals.php');
        foreach ($manuals as $key => $manual) {
            $pdf = file_get_contents(base_path('docs/manuals/FNU-Job-Placement-'.$key.'-Manual.pdf'));
            $this->assertSame(1, preg_match('/\/Type\s*\/Pages\b[^>]*\/Count\s+(\d+)/s', $pdf, $matches));
            $this->assertSame(1 + count($manual['sections']), (int) $matches[1], $key);
        }
    }
}
