@extends('layout.main')

@section('content')
<section class="forms">
    <div class="container-fluid jb-shell">
        @include('membership.partials.tabs')

        <div class="mb-4">
            <h1 class="jb-title">Membership</h1>
            <p class="jb-subtitle">Review CWA Cameroon membership requests. Approval creates a Letter of Admission for the National President to sign.</p>
        </div>

        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif
        @if(session('not_permitted'))
            <div class="alert alert-danger">{{ session('not_permitted') }}</div>
        @endif

        <form method="GET" class="jb-card">
            <div class="row align-items-end">
                <div class="col-md-6 mb-2">
                    <label class="jb-label">Search</label>
                    <input type="search" name="q" value="{{ $q }}" class="jb-field" placeholder="Name, phone, diocese, parish…">
                </div>
                <div class="col-md-2 mb-2">
                    <button type="submit" class="jb-btn" style="width:100%;justify-content:center;">Filter</button>
                </div>
            </div>
        </form>

        <div class="jb-card">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Diocese / Parish</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $row)
                            <tr>
                                <td>
                                    @if($row->portrait_path)
                                        <img src="{{ url('/'.$row->portrait_path) }}" alt="" class="mem-portrait">
                                    @endif
                                </td>
                                <td><strong>{{ $row->name }}</strong></td>
                                <td>{{ $row->phone }}</td>
                                <td>{{ $row->diocese }}<br><span class="text-muted">{{ $row->parish }}</span></td>
                                <td>{{ $row->created_at ? $row->created_at->format('d M Y') : '—' }}</td>
                                <td>
                                    @if($row->status === 'approved')
                                        <span class="jb-badge ok">Approved</span>
                                    @elseif($row->status === 'rejected')
                                        <span class="jb-badge no">Rejected</span>
                                    @else
                                        <span class="jb-badge wait">Awaiting</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('membership.show', $row->id) }}" class="btn btn-sm btn-primary">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No records.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(method_exists($items, 'links'))
                <div class="mt-3">{{ $items->links() }}</div>
            @endif
        </div>
    </div>
</section>
@endsection
