<?php

namespace App\Services\Reporting;

use Illuminate\Http\Request;

class ReportPdfRenderer
{
    public function render(string $title, array $rows, ?Request $request = null, array $headers = []): string
    {
        $firstRow = $rows[0] ?? null;
        $isAssociative = is_array($firstRow) && ! array_is_list($firstRow);
        $bodyRows = $rows;

        if ($isAssociative) {
            $headers = array_keys($firstRow);
        } elseif ($firstRow === ['Metric', 'Value']) {
            $headers = $firstRow;
            $bodyRows = array_slice($rows, 1);
        } elseif ($firstRow !== null) {
            $headers = [];
        }

        return view('admin.reports.export', [
            'title' => $title,
            'headers' => $headers,
            'rows' => $bodyRows,
            'downloadedBy' => $request?->user()
                ? $request->user()->name.' ('.$request->user()->email.')'
                : 'System',
            'downloadedAt' => now()->timezone(config('reporting.timezone'))->format('F j, Y \a\t g:i A e'),
        ])->render();
    }
}
