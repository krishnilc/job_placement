<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    /**
     * List all feedback submitted by employers and students. Restricted to
     * admin/super_admin via the "checkAdminOrSuperAdmin" route middleware.
     */
    public function index(Request $request)
    {
        $feedbacks = $this->feedbackQuery($request)->paginate(15)->withQueryString();

        return view('admin.feedback.list', [
            'feedbacks' => $feedbacks,
            'typeFilter' => $request->query('type'),
            'sort' => $request->query('sort') ?: 'created_at',
            'direction' => $request->query('direction') ?: 'desc',
        ]);
    }

    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['pdf', 'excel'], true), 404);
        $query = $this->feedbackQuery($request);
        $headers = ['Job', 'Company', 'Type', 'From', 'About', 'Rating', 'Comments', 'Submitted'];

        if ($format === 'pdf') {
            return Pdf::loadView('admin.feedback.export', [
                'headers' => $headers,
                'rows' => $query->get()->map(fn (Feedback $feedback) => $this->exportRow($feedback)),
                'generatedAt' => now()->format('M d, Y g:i A'),
            ])->setPaper('a4', 'landscape')->download('feedback-report.pdf');
        }

        return response()->streamDownload(function () use ($query, $headers) {
            $stream = fopen('php://output', 'w');
            if ($stream === false) {
                throw new \RuntimeException('Unable to open feedback export stream.');
            }

            try {
                // UTF-8 BOM lets Excel display international names and comments correctly.
                if (fwrite($stream, "\xEF\xBB\xBF") === false || fputcsv($stream, $headers) === false) {
                    throw new \RuntimeException('Unable to write feedback export headers.');
                }
                foreach ($query->lazy(500) as $feedback) {
                    $row = array_map(function ($value) {
                        // Prevent user-submitted text from being interpreted as spreadsheet formulas.
                        return is_string($value) && preg_match('/^[\s]*[=+\-@]/u', $value)
                            ? "'".$value
                            : $value;
                    }, $this->exportRow($feedback));
                    if (fputcsv($stream, $row) === false) {
                        throw new \RuntimeException('Unable to write feedback export row.');
                    }
                }
            } finally {
                fclose($stream);
            }
        }, 'feedback-report.csv', ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    private function exportRow(Feedback $feedback): array
    {
        return [
            $feedback->jobApplication?->job?->title ?? 'N/A',
            $feedback->jobApplication?->job?->company_name ?? 'N/A',
            $feedback->feedback_type === Feedback::TYPE_EMPLOYER_TO_STUDENT ? 'Employer -> Student' : 'Student -> Company',
            $feedback->givenBy?->name ?? 'Unknown',
            $feedback->givenTo?->name ?? 'N/A',
            $feedback->rating ?: 'N/A',
            $feedback->comments ?: '',
            $feedback->created_at?->format('M d, Y g:i A') ?? 'N/A',
        ];
    }

    private function feedbackQuery(Request $request): Builder
    {
        $sortableColumns = [
            'title' => 'jobs.title',
            'company_name' => 'jobs.company_name',
            'feedback_type' => 'feedbacks.feedback_type',
            'given_by' => 'feedback_authors.name',
            'given_to' => 'feedback_recipients.name',
            'rating' => 'feedbacks.rating',
            'comments' => 'feedbacks.comments',
            'created_at' => 'feedbacks.created_at',
        ];
        $request->validate([
            'sort' => ['nullable', Rule::in(array_keys($sortableColumns))],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $sort = $request->query('sort') ?: 'created_at';
        $direction = $request->query('direction') ?: 'desc';
        $typeFilter = $request->query('type');
        $search = trim((string) $request->query('search', ''));

        return Feedback::query()
            ->select('feedbacks.*')
            ->leftJoin('job_applications', 'job_applications.id', '=', 'feedbacks.job_application_id')
            ->leftJoin('jobs', 'jobs.id', '=', 'job_applications.job_id')
            ->leftJoin('users as feedback_authors', 'feedback_authors.id', '=', 'feedbacks.given_by')
            ->leftJoin('users as feedback_recipients', 'feedback_recipients.id', '=', 'feedbacks.given_to')
            ->with([
                'givenBy:id,name,email,role',
                'givenTo:id,name,email,role',
                'jobApplication.job:id,title,company_name',
            ])
            ->when($typeFilter, fn ($query) => $query->where('feedbacks.feedback_type', $typeFilter))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.$search.'%';
                $query->where(function ($q) use ($like) {
                    $q->whereHas('givenBy', fn ($u) => $u->where('name', 'like', $like))
                        ->orWhereHas('givenTo', fn ($u) => $u->where('name', 'like', $like))
                        ->orWhereHas('jobApplication.job', fn ($j) => $j->where('title', 'like', $like)->orWhere('company_name', 'like', $like));
                });
            })
            ->orderBy($sortableColumns[$sort], $direction)
            ->when($sort === 'title', fn ($query) => $query->orderBy('jobs.company_name', $direction))
            ->orderByDesc('feedbacks.id');
    }
}
