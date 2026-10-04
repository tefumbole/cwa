@php
    $fails = array_values(array_filter($report['rows'], function ($row) { return $row['result'] === 'fails'; }));
    $works = array_values(array_filter($report['rows'], function ($row) { return $row['result'] === 'works'; }));
@endphp
<p><strong>{{ $report['tester_name'] }}</strong> finished a CWACAM system test on {{ $report['created_at'] }}.</p>
@if($report['tester_email'] !== '')
    <p>Tester email: {{ $report['tester_email'] }}</p>
@endif
<p>
    Works: {{ $report['counts']['works'] }}
    &nbsp;·&nbsp; Does not work: {{ $report['counts']['fails'] }}
    &nbsp;·&nbsp; Not tested: {{ $report['counts']['skipped'] }}
    &nbsp;·&nbsp; Total: {{ $report['total'] }}
</p>
@if($report['summary'] !== '')
    <p><strong>Overall note</strong><br>{{ $report['summary'] }}</p>
@endif

<h3>Does not work</h3>
@if(count($fails) === 0)
    <p>Nothing was marked as failing.</p>
@else
    <ul>
        @foreach($fails as $row)
            <li>
                <strong>{{ $row['section'] }}.</strong> {{ $row['text'] }}
                @if($row['note'] !== '')
                    <br>Note: {{ $row['note'] }}
                @endif
            </li>
        @endforeach
    </ul>
@endif

<h3>Works</h3>
@if(count($works) === 0)
    <p>Nothing was marked as working.</p>
@else
    <ul>
        @foreach($works as $row)
            <li>{{ $row['section'] }} — {{ $row['text'] }}@if($row['note'] !== '') ({{ $row['note'] }})@endif</li>
        @endforeach
    </ul>
@endif
<p>Report {{ $report['id'] }} is also saved in the admin area under Help → Test results.</p>
