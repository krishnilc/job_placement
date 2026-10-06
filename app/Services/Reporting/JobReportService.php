<?php

namespace App\Services\Reporting;

use App\Models\College;
use App\Models\Job;
use App\Models\JobType;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JobReportService
{
    public function buildJobs(Request $request, Builder $applications): array
    {
        $counts = (clone $applications)
            ->select('job_applications.job_id', DB::raw('COUNT(job_applications.id) as application_count'))
            ->groupBy('job_applications.job_id');
        $jobs = Job::query()
            ->leftJoin('organizations', 'organizations.id', '=', 'jobs.organization_id')
            ->leftJoinSub($counts, 'job_report_counts', fn ($join) => $join->on('job_report_counts.job_id', '=', 'jobs.id'))
            ->select('jobs.*', DB::raw('COALESCE(organizations.name, jobs.company_name) as organization_name'),
                DB::raw('COALESCE(job_report_counts.application_count, 0) as application_count'))
            ->with('jobType')
            ->when($request->filled('report_organization'), fn ($query) => $query->where('jobs.organization_id', $request->input('report_organization')))
            ->when($request->filled('employer'), fn ($query) => $query->where('jobs.user_id', $request->input('employer')))
            ->when($request->filled('opportunity_type'), fn ($query) => $query->where('jobs.job_type_id', $request->input('opportunity_type')))
            ->when($request->filled('category'), fn ($query) => $query->where('jobs.category_id', $request->input('category')))
            ->orderBy('organization_name')->orderBy('jobs.created_at', 'desc')->orderBy('jobs.id', 'desc')
            ->get();

        return [
            'jobLevelReports' => $jobs->map(fn ($job) => [
                'Job ID' => $job->id,
                'Organization' => $job->organization_name,
                'Job Title' => $job->title,
                'Job Type' => $job->jobType?->name ?? 'Not provided',
                'Location' => $job->location,
                'Vacancies' => $job->vacancy,
                'Status' => match ((int) $job->status) {
                    0 => 'Pending',
                    1 => 'Active',
                    2 => 'Blocked',
                },
                'Posted' => $job->created_at->format('d M Y'),
                'Closing Date' => $job->closing_date ?: 'Not provided',
                'Applications' => (int) $job->application_count,
            ]),
            'jobReportOrganizationOptions' => Organization::orderBy('name')->get(['id', 'name']),
        ];
    }

    public function buildJobTypes(Request $request): array
    {
        $types = JobType::orderBy('name')->get(['id', 'name', 'status']);
        $colleges = College::orderBy('name')->get(['id', 'name']);
        $selectedType = $request->filled('report_job_type')
            ? $types->firstWhere('id', (int) $request->input('report_job_type'))
            : $types->first(fn ($type) => strtolower(trim($type->name)) === 'industrial attachment');

        $jobs = Job::query()
            ->leftJoin('categories', 'categories.id', '=', 'jobs.category_id')
            ->when($request->filled('report_college'), fn ($query) => $query->where('categories.college_id', $request->input('report_college')));
        $typeCounts = $this->jobReportCounts(clone $jobs, 'jobs.job_type_id')->keyBy('group_id');
        $collegeCounts = $selectedType
            ? $this->jobReportCounts((clone $jobs)->where('jobs.job_type_id', $selectedType->id), 'categories.college_id')->keyBy('group_id')
            : collect();

        $makeReport = function ($id, $name, $counts) {
            $applications = (int) ($counts?->application_count ?? 0);
            $placed = (int) ($counts?->placed_count ?? 0);

            return (object) [
                'id' => $id,
                'name' => $name,
                'job_count' => (int) ($counts?->job_count ?? 0),
                'application_count' => $applications,
                'placed_count' => $placed,
                'rejected_count' => (int) ($counts?->rejected_count ?? 0),
                'placement_rate' => $applications > 0 ? round($placed / $applications * 100, 2) : 0,
            ];
        };

        $typeReports = $types
            ->when($request->filled('report_job_type'), fn ($rows) => $rows->where('id', (int) $request->input('report_job_type')))
            ->map(fn ($type) => $makeReport($type->id, $type->name, $typeCounts->get($type->id)))
            ->values();
        $collegeReports = $selectedType
            ? $colleges
                ->when($request->filled('report_college'), fn ($rows) => $rows->where('id', (int) $request->input('report_college')))
                ->map(fn ($college) => $makeReport($college->id, $college->name, $collegeCounts->get($college->id)))
                ->values()
            : collect();
        if ($selectedType && $collegeCounts->has('')) {
            $collegeReports->push($makeReport(null, 'Unassigned college', $collegeCounts->get('')));
        }

        return [
            'jobTypeReports' => $typeReports,
            'jobTypeCollegeReports' => $collegeReports,
            'jobTypeCollegeOptions' => $colleges,
            'selectedReportCollege' => $request->filled('report_college')
                ? $colleges->firstWhere('id', (int) $request->input('report_college'))
                : null,
            'selectedReportJobType' => $selectedType,
        ];
    }

    private function jobReportCounts(Builder $jobs, string $groupColumn): Collection
    {
        // Count jobs separately from application rows so multiple applicants do not inflate jobs.
        return $jobs
            ->leftJoin('job_applications as report_applications', 'report_applications.job_id', '=', 'jobs.id')
            ->leftJoin('application_statuses as report_statuses', 'report_statuses.id', '=', 'report_applications.application_status_id')
            ->select(
                $groupColumn.' as group_id',
                DB::raw('COUNT(DISTINCT jobs.id) as job_count'),
                DB::raw('COUNT(report_applications.id) as application_count'),
                DB::raw("SUM(CASE WHEN report_statuses.name = 'Placed' THEN 1 ELSE 0 END) as placed_count"),
                DB::raw("SUM(CASE WHEN report_statuses.name = 'Rejected' THEN 1 ELSE 0 END) as rejected_count")
            )
            ->groupBy($groupColumn)
            ->get();
    }
}
