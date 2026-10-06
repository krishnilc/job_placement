<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4 landscape; margin: 40px 40px 60px 40px; }
        body { font-family: Arial, sans-serif; padding: 0; margin: 0; font-size: 12px; color: #000; }
        .brand { font-size: 22px; font-weight: 700; color: #174a68; margin: 0 0 2px; text-align: left; }
        .report-name { font-size: 15px; font-weight: 600; color: #444; margin: 0 0 6px; text-align: left; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 12px; }
        th { background: #f3f4f6; font-weight: 700; }
        .report-footer { position: fixed; bottom: -40px; left: 0; right: 0; height: 40px; border-top: 1px solid #ddd; padding-top: 6px; font-size: 10px; color: #555; }
        .report-footer .left { float: left; }
        @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>
    <div class="brand">FNU Job Placement</div>
    <div class="report-name">{{ $title }} Report</div>
    <table>
        @if ($headers !== [])
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    @foreach ($row as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="report-footer">
        <span class="left">Downloaded: {{ $downloadedAt }} &nbsp;|&nbsp; Downloaded by: {{ $downloadedBy }}</span>
    </div>
</body>
</html>
