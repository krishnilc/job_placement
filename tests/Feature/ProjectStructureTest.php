<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProjectStructureTest extends TestCase
{
    public function test_application_namespaces_and_class_names_match_their_case_sensitive_paths(): void
    {
        foreach (File::allFiles(app_path()) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $source = $file->getContents();
            $relative = str_replace('\\', '/', $file->getRelativePathname());
            $directory = dirname($relative);
            $expectedNamespace = $directory === '.' ? 'App' : 'App\\'.str_replace('/', '\\', $directory);
            $this->assertSame(1, preg_match('/^namespace\s+([^;]+);/m', $source, $namespace), $relative);
            $this->assertSame($expectedNamespace, $namespace[1], $relative);
            $this->assertSame(1, preg_match('/^(?:(?:abstract|final)\s+)?class\s+(\w+)/m', $source, $class), $relative);
            $this->assertSame($file->getBasename('.php'), $class[1], $relative);
            foreach (explode('\\', $expectedNamespace) as $segment) {
                $this->assertMatchesRegularExpression('/^[A-Z][A-Za-z0-9]*$/', $segment, $relative);
            }
        }
    }

    public function test_blade_filenames_are_kebab_case_and_literal_view_references_exist(): void
    {
        foreach (File::allFiles(resource_path('views')) as $file) {
            $this->assertMatchesRegularExpression('/^[a-z0-9]+(?:-[a-z0-9]+)*\.blade\.php$/', $file->getFilename());
        }

        foreach ([app_path(), resource_path('views')] as $directory) {
            foreach (File::allFiles($directory) as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }
                preg_match_all('/(?:\bview|@include|@extends)\(\s*[\'"]([a-zA-Z0-9.-]+)[\'"]/', $file->getContents(), $references);
                foreach ($references[1] as $view) {
                    $this->assertTrue(view()->exists($view), $file->getRelativePathname().' references missing view '.$view);
                }
            }
        }
    }

    public function test_existing_route_names_resolve_to_the_reorganized_controllers(): void
    {
        foreach ([
            'account.authenticate' => 'App\\Http\\Controllers\\AuthController@authenticate',
            'student.dashboard' => 'App\\Http\\Controllers\\Student\\DashboardController@index',
            'admin.dashboard' => 'App\\Http\\Controllers\\Admin\\DashboardController@index',
            'account.profile' => 'App\\Http\\Controllers\\AccountController@viewProfile',
            'account.myJobApplications' => 'App\\Http\\Controllers\\Student\\JobApplicationController@index',
            'account.savedJobs' => 'App\\Http\\Controllers\\Student\\SavedJobController@index',
            'account.createJob' => 'App\\Http\\Controllers\\Employer\\JobController@createJob',
            'front.jobs' => 'App\\Http\\Controllers\\JobController@index',
        ] as $name => $action) {
            $this->assertSame($action, Route::getRoutes()->getByName($name)->getActionName());
        }
    }

    public function test_metric_card_preserves_the_value_color_and_icon_and_escapes_text(): void
    {
        $html = Blade::render('<x-dashboard.metric-card label="<script>alert(1)</script>" :value="0" color="warning" icon="fa-hourglass-half" />');
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('<h2 class="text-warning mb-0">0</h2>', $html);
        $this->assertStringContainsString('fa fa-hourglass-half', $html);
        $this->assertStringContainsString('col-md-6 col-lg-3 mb-3', $html);
    }
}
