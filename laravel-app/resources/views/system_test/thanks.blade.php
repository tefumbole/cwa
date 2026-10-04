<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Test result sent</title>
    <style>
        body { margin:0; font-family: Nunito, system-ui, sans-serif; background:#f6f3ec; color:#1a1f2e; }
        main { max-width:760px; margin:0 auto; padding:28px 16px 48px; }
        .card { background:#fff; border:1px solid #e6e1d6; border-radius:14px; padding:18px; }
        h1 { color:#0b3f90; margin:0 0 8px; }
        .nums { display:flex; gap:10px; flex-wrap:wrap; margin:14px 0; }
        .nums div { background:#f6f3ec; border-radius:10px; padding:10px 12px; min-width:120px; }
        .nums strong { display:block; font-size:22px; color:#0b3f90; }
        li { margin:0 0 8px; }
        .warn { background:#fff4d6; padding:10px 12px; border-radius:8px; }
    </style>
</head>
<body>
<main>
    <div class="card">
        <h1>Result recorded</h1>
        <p>Thank you, {{ $report['tester_name'] }}. Report <strong>{{ $report['id'] }}</strong>.</p>
        @if($mailed)
            <p>A copy was emailed to {{ implode(', ', $recipients) }}.</p>
        @else
            <p class="warn">The result is saved, but the email was not sent. Ask an administrator to open Help → Test results.</p>
        @endif
        <div class="nums">
            <div><strong>{{ $report['counts']['works'] }}</strong>Works</div>
            <div><strong>{{ $report['counts']['fails'] }}</strong>Does not work</div>
            <div><strong>{{ $report['counts']['skipped'] }}</strong>Not tested</div>
        </div>
        <h2>Does not work</h2>
        <ul>
            @php $fails = array_filter($report['rows'], function ($row) { return $row['result'] === 'fails'; }); @endphp
            @forelse($fails as $row)
                <li><strong>{{ $row['section'] }}.</strong> {{ $row['text'] }}@if($row['note'] !== '') — {{ $row['note'] }}@endif</li>
            @empty
                <li>Nothing was marked as failing.</li>
            @endforelse
        </ul>
    </div>
</main>
</body>
</html>
