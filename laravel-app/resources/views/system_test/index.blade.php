@extends('layout.main')

@section('content')
<section>
    <div class="container-fluid">
        <h3 class="mb-1" style="color:#0b3f90;font-weight:800;">System test results</h3>
        <p class="text-muted">These are the reports people sent from the shared test link.</p>
        <p><a class="btn btn-primary btn-sm" href="{{ route('system-test.show') }}" target="_blank">Open the test link</a></p>
        <div class="card">
            <div class="card-body table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr><th>When</th><th>Tester</th><th>Works</th><th>Does not work</th><th>Not tested</th><th></th></tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr>
                                <td>{{ $report['created_at'] }}</td>
                                <td>{{ $report['tester_name'] }}</td>
                                <td>{{ $report['counts']['works'] ?? 0 }}</td>
                                <td>{{ $report['counts']['fails'] ?? 0 }}</td>
                                <td>{{ $report['counts']['skipped'] ?? 0 }}</td>
                                <td><a href="{{ route('system-test.detail', $report['id']) }}">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6">No results yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
