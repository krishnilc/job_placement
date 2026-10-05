<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Feedback;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FeedbackSortingTest extends TestCase
{
    use RefreshDatabase;

    private function makeFeedback(string $prefix, string $type, int $rating): Feedback
    {
        $author = User::factory()->create(['name' => $prefix.' Author']);
        $recipient = User::factory()->create(['name' => $prefix.' Recipient']);
        $job = Job::factory()->create([
            'title' => $prefix.' Job',
            'company_name' => $prefix.' Company',
            'user_id' => $author->id,
            'job_type_id' => JobType::factory(),
            'category_id' => Category::factory(),
        ]);
        $application = JobApplication::create([
            'job_id' => $job->id,
            'user_id' => $recipient->id,
            'employer_id' => $author->id,
        ]);
        $feedback = Feedback::create([
            'job_application_id' => $application->id,
            'given_by' => $author->id,
            'given_to' => $recipient->id,
            'feedback_type' => $type,
            'rating' => $rating,
            'comments' => $prefix.' Comments',
        ]);
        $feedback->created_at = $prefix === 'Alpha' ? '2026-01-01 12:00:00' : '2026-02-01 12:00:00';
        $feedback->save();

        return $feedback;
    }

    public function test_every_heading_sorts_both_directions_and_default_is_newest_first(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $alpha = $this->makeFeedback('Alpha', Feedback::TYPE_EMPLOYER_TO_STUDENT, 1);
        $zulu = $this->makeFeedback('Zulu', Feedback::TYPE_STUDENT_TO_EMPLOYER, 5);
        $this->actingAs($admin)->get(route('admin.feedback'))->assertOk()
            ->assertViewHas('feedbacks', fn ($rows) => $rows->pluck('id')->all() === [$zulu->id, $alpha->id]);

        foreach (['title', 'company_name', 'feedback_type', 'given_by', 'given_to', 'rating', 'comments', 'created_at'] as $column) {
            foreach (['asc', 'desc'] as $direction) {
                $expected = $direction === 'asc' ? [$alpha->id, $zulu->id] : [$zulu->id, $alpha->id];
                $response = $this->get(route('admin.feedback', ['sort' => $column, 'direction' => $direction]));
                $response->assertOk()
                    ->assertViewHas('feedbacks', fn ($rows) => $rows->pluck('id')->all() === $expected)
                    ->assertSee('aria-sort="'.($direction === 'asc' ? 'ascending' : 'descending').'"', false);
                $this->assertSame($column, $response->viewData('sort'));
            }
        }
    }

    public function test_sorting_preserves_filters_and_resets_page_and_pagination_preserves_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $feedback = $this->makeFeedback('Alpha', Feedback::TYPE_EMPLOYER_TO_STUDENT, 1);
        $this->makeFeedback('Zulu', Feedback::TYPE_STUDENT_TO_EMPLOYER, 5);
        foreach (range(1, 16) as $index) {
            $application = $feedback->jobApplication->replicate();
            $application->save();
            $copy = $feedback->replicate();
            $copy->job_application_id = $application->id;
            $copy->given_by = User::factory()->create()->id;
            $copy->save();
        }
        $query = [
            'sort' => 'rating', 'direction' => 'asc', 'search' => 'Alpha',
            'type' => Feedback::TYPE_EMPLOYER_TO_STUDENT, 'page' => 2,
        ];
        $response = $this->actingAs($admin)->get(route('admin.feedback', $query))->assertOk();
        $rows = $response->viewData('feedbacks');
        $this->assertSame(17, $rows->total());
        $this->assertCount(2, $rows->items());
        $this->assertStringContainsString('sort=rating', $rows->url(1));
        $this->assertStringContainsString('direction=asc', $rows->url(1));
        $this->assertStringContainsString('search=Alpha', $rows->url(1));
        $query['sort'] = 'given_by';
        unset($query['page']);
        $response->assertSee(route('admin.feedback', $query));
        $response->assertSee('name="sort" value="rating"', false);
        $response->assertSee('name="direction" value="asc"', false);
    }

    public function test_invalid_sort_parameters_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->getJson(route('admin.feedback', [
            'sort' => 'unknown', 'direction' => 'invalid',
        ]))->assertUnprocessable()->assertJsonValidationErrors(['sort', 'direction']);
    }

    public function test_excel_exports_all_matching_pages_in_selected_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $feedback = $this->makeFeedback('Alpha', Feedback::TYPE_EMPLOYER_TO_STUDENT, 1);
        $this->makeFeedback('Zulu', Feedback::TYPE_STUDENT_TO_EMPLOYER, 5);
        foreach (range(1, 16) as $index) {
            $application = $feedback->jobApplication->replicate();
            $application->save();
            $copy = $feedback->replicate();
            $copy->job_application_id = $application->id;
            $copy->rating = 5;
            $copy->comments = " =1+1\nSecond line, \"quoted\"";
            $copy->save();
        }
        $filters = [
            'search' => 'Alpha', 'type' => Feedback::TYPE_EMPLOYER_TO_STUDENT,
            'sort' => 'rating', 'direction' => 'desc',
        ];
        $this->actingAs($admin)->get(route('admin.feedback', array_merge($filters, ['page' => 2])))
            ->assertOk()
            ->assertSee(route('admin.feedback.export', array_merge($filters, ['format' => 'pdf'])))
            ->assertSee(route('admin.feedback.export', array_merge($filters, ['format' => 'excel'])));
        $response = $this->get(route('admin.feedback.export', array_merge($filters, ['format' => 'excel', 'page' => 2])));
        $response->assertOk()->assertDownload('feedback-report.csv')
            ->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $contents = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $contents);
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, substr($contents, 3));
        rewind($stream);
        $rows = [];
        while (($row = fgetcsv($stream)) !== false) {
            $rows[] = $row;
        }
        fclose($stream);
        $this->assertCount(18, $rows);
        $this->assertSame(['Job', 'Company', 'Type', 'From', 'About', 'Rating', 'Comments', 'Submitted'], $rows[0]);
        $this->assertSame('5', $rows[1][5]);
        $this->assertSame("' =1+1\nSecond line, \"quoted\"", $rows[1][6]);
        $this->assertSame('1', $rows[17][5]);
        $this->assertStringNotContainsString('Zulu', $contents);
    }

    public function test_pdf_export_downloads_filtered_feedback_and_empty_exports_work(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->makeFeedback('Alpha', Feedback::TYPE_EMPLOYER_TO_STUDENT, 1);
        $response = $this->actingAs($admin)->get(route('admin.feedback.export', ['format' => 'pdf', 'search' => 'Alpha']));
        $response->assertOk()->assertDownload('feedback-report.pdf')->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->get(route('admin.feedback.export', ['format' => 'pdf', 'search' => 'No match']))
            ->assertOk()->assertDownload('feedback-report.pdf');
        $empty = $this->get(route('admin.feedback.export', ['format' => 'excel', 'search' => 'No match']));
        $empty->assertOk();
        $this->assertSame(1, substr_count($empty->streamedContent(), "\n"));
    }

    public function test_exports_restrict_access_and_validate_parameters(): void
    {
        foreach (['pdf', 'excel'] as $format) {
            $url = route('admin.feedback.export', ['format' => $format]);
            $this->get($url)->assertRedirect();
            foreach (['employer', 'student'] as $role) {
                $this->actingAs(User::factory()->create(['role' => $role]))->get($url)->assertRedirect();
            }
            $this->actingAs(User::factory()->create(['role' => 'admin']))
                ->getJson($url.'?sort=unknown&direction=invalid')
                ->assertUnprocessable()->assertJsonValidationErrors(['sort', 'direction']);
            $this->app['auth']->forgetGuards();
        }
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.feedback.export', ['format' => 'unknown']))->assertNotFound();
    }

    public function test_feedback_timestamps_use_fiji_time_in_list_pdf_and_excel(): void
    {
        config(['reporting.timezone' => 'Pacific/Fiji']);
        $this->travelTo(Carbon::parse('2026-10-05 03:08:00', 'UTC'));
        $admin = User::factory()->create(['role' => 'admin']);
        $feedback = $this->makeFeedback('Alpha', Feedback::TYPE_EMPLOYER_TO_STUDENT, 1);
        $feedback->created_at = '2026-10-05 03:08:00';
        $feedback->save();
        $expected = 'Oct 05, 2026 3:08 PM Pacific/Fiji';
        $this->actingAs($admin)->get(route('admin.feedback'))->assertOk()->assertSee($expected);
        $csv = $this->get(route('admin.feedback.export', ['format' => 'excel']))->assertOk();
        $this->assertStringContainsString($expected, $csv->streamedContent());

        Pdf::shouldReceive('loadView')->once()->andReturnUsing(function ($view, $data) use ($expected) {
            $this->assertSame($expected, $data['generatedAt']);
            $this->assertSame($expected, $data['rows']->first()[7]);
            $pdf = new \Barryvdh\DomPDF\PDF(app('dompdf'), app('config'), app('files'), app('view'));

            return $pdf->loadView($view, $data);
        });
        $this->get(route('admin.feedback.export', ['format' => 'pdf']))
            ->assertOk()->assertDownload('feedback-report.pdf');
        $this->assertSame('2026-10-05 03:08:00', $feedback->fresh()->getRawOriginal('created_at'));
        $this->travelBack();
    }
}
