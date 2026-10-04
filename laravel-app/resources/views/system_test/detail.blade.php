@extends('layout.main')

@section('content')
<section>
    <div class="container-fluid">
        <p><a href="{{ route('system-test.index') }}">All results</a></p>
        <h3 style="color:#0b3f90;font-weight:800;">{{ $report['tester_name'] }} — {{ $report['created_at'] }}</h3>
        <p>
            Works {{ $report['counts']['works'] }}
            · Does not work {{ $report['counts']['fails'] }}
            · Not tested {{ $report['counts']['skipped'] }}
            @if(!empty($report['tester_phone'])) · {{ $report['tester_phone'] }} @endif
        </p>
        @if($report['summary'] !== '')
            <p><strong>Overall note.</strong> {{ $report['summary'] }}</p>
        @endif
        @php
            $order = ['fails' => 'Does not work', 'works' => 'Works', 'skipped' => 'Not tested'];
        @endphp
        @foreach($order as $key => $heading)
            <h4 class="mt-4">{{ $heading }}</h4>
            <ul>
                @foreach($report['rows'] as $row)
                    @if($row['result'] === $key)
                        <li><strong>{{ $row['section'] }}.</strong> {{ $row['text'] }}@if($row['note'] !== '') — {{ $row['note'] }}@endif</li>
                    @endif
                @endforeach
            </ul>
        @endforeach
    </div>
</section>
@endsection
