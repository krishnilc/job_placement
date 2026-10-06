<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DashboardReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'report_job_type' => ['nullable', 'integer', 'exists:job_types,id'],
            'report_college' => ['nullable', 'integer', 'exists:colleges,id'],
            'report_organization' => ['nullable', 'integer', 'exists:organizations,id'],
            'funnel_job' => ['nullable', 'integer', Rule::exists('jobs', 'id')->where(function ($query) {
                if ($this->filled('report_organization')) {
                    $query->where('organization_id', $this->input('report_organization'));
                }
            })],
        ];
    }
}
