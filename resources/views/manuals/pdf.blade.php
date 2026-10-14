<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $manual['title'] }}</title>
    <style>
        @page { margin: 50px 48px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; line-height: 1.5; color: #243247; }
        h1 { font-size: 28pt; color: #163b65; }
        h2 { font-size: 15pt; color: #163b65; page-break-after: avoid; }
        p { margin: 0 0 12px; }
        .cover { padding-top: 60px; }
        .section { page-break-before: always; }
        .footer { position: fixed; bottom: -28px; font-size: 8pt; color: #666; }
        a { color: #163b65; }
    </style>
</head>
<body>
    <div class="footer">FNU Job Placement | {{ $manual['title'] }} | Version 2.0</div>
    <div class="cover">
        <p>FNU Job Placement</p>
        <h1>{{ $manual['title'] }}</h1>
        <p>{{ $manual['audience'] }}</p>
        <p>Version 2.0 | 14 October 2026</p>
        <p>Use the portal address supplied by FNU. This guide describes the current site; available actions depend on your account role.</p>
        <h2>Contents</h2>
        <ol>
            @foreach (array_keys($manual['sections']) as $index => $heading)
                <li><a href="#section-{{ $index }}">{{ $heading }}</a></li>
            @endforeach
        </ol>
    </div>
    @foreach ($manual['sections'] as $heading => $paragraphs)
        <div class="section">
            <h2 id="section-{{ $loop->index }}">{{ $loop->iteration }}. {{ $heading }}</h2>
            @foreach ($paragraphs as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>
    @endforeach
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_text(470, 810, "{PAGE_NUM} / {PAGE_COUNT}", null, 8, [0.4, 0.4, 0.4]);
        }
    </script>
</body>
</html>
