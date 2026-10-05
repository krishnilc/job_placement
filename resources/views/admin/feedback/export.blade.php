<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Feedback Report</title>
    <style>
        @page { margin: 25px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1 { font-size: 18px; color: #174a68; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; overflow-wrap: break-word; }
        th { background: #f3f4f6; }
        thead { display: table-header-group; }
        td { vertical-align: top; white-space: pre-wrap; }
        .comments { width: 25%; }
    </style>
</head>
<body>
    <h1>FNU Job Placement - Feedback Report</h1>
    <p>Generated: {{ $generatedAt }} | Matching feedback: {{ $rows->count() }}</p>
    <table>
        <thead>
            <tr>
                @foreach ($headers as $header)
                    <th @class(['comments' => $header === 'Comments'])>{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="8">No feedback matches the selected filters.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
