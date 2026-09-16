@extends('layout.main')

@section('content')
<section class="forms">
    <div class="container-fluid jb-shell">
        @include('membership.partials.tabs')

        @if(session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        <div class="d-flex justify-content-between align-items-start flex-wrap mb-3" style="gap:12px;">
            <div>
                <h1 class="jb-title">{{ $member->name }}</h1>
                <p class="jb-subtitle">{{ $member->phone }} · {{ $member->email ?: 'No email' }}</p>
            </div>
            <a href="{{ route($membershipTab) }}" class="btn btn-outline-secondary">Back</a>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="jb-card text-center">
                    @if($member->portrait_path)
                        <img src="{{ url('/'.$member->portrait_path) }}" alt="" style="width:220px;height:220px;border-radius:50%;object-fit:cover;border:4px solid #c6ab47;background:#003D82;">
                    @endif
                    <p class="mt-3 mb-0"><span class="jb-badge {{ $member->status === 'approved' ? 'ok' : ($member->status === 'rejected' ? 'no' : 'wait') }}">{{ str_replace('_', ' ', $member->status) }}</span></p>
                    @if($member->admitted_year)
                        <p class="mt-2 mb-0">Year of joining: <strong>{{ $member->admitted_year }}</strong></p>
                    @endif
                </div>
            </div>
            <div class="col-md-8">
                <div class="jb-card">
                    <p><strong>Diocese:</strong> {{ $member->diocese }}</p>
                    <p><strong>Parish:</strong> {{ $member->parish }}</p>
                    <p><strong>Age range:</strong> {{ $member->age_range ?: '—' }}</p>
                    <p><strong>Bylaws agreed:</strong> {{ $member->bylaws_agreed_at ? $member->bylaws_agreed_at->format('d M Y H:i') : '—' }}</p>
                    @if($member->letter_id)
                        <p><strong>Admission letter:</strong> <a href="{{ url('/letters/show/'.$member->letter_id) }}">#{{ $member->letter_id }}</a></p>
                    @endif
                    @if($member->rejection_reason)
                        <p><strong>Rejection reason:</strong> {{ $member->rejection_reason }}</p>
                    @endif
                </div>

                <div class="jb-card">
                    <h5>Original selfie</h5>
                    @if($member->selfie_path)
                        <img src="{{ url('/'.$member->selfie_path) }}" alt="" style="max-width:280px;border-radius:10px;">
                    @else
                        <p class="text-muted">None</p>
                    @endif
                </div>

                <div class="jb-card">
                    <h5>National ID</h5>
                    <div class="d-flex flex-wrap" style="gap:12px;">
                        @if($member->id_front_path)
                            <div><p class="mb-1">Front</p><img src="{{ url('/'.$member->id_front_path) }}" alt="" style="max-width:240px;border-radius:10px;"></div>
                        @endif
                        @if($member->id_back_path)
                            <div><p class="mb-1">Back</p><img src="{{ url('/'.$member->id_back_path) }}" alt="" style="max-width:240px;border-radius:10px;"></div>
                        @endif
                        @if(! $member->id_front_path && ! $member->id_back_path)
                            <p class="text-muted mb-0">Not provided</p>
                        @endif
                    </div>
                </div>

                <div class="jb-card">
                    <h5>Member signature</h5>
                    @if($member->signature_path)
                        <div style="background:#fff;border:1px dashed #cbd5e1;border-radius:10px;padding:12px;display:inline-block;">
                            <img src="{{ url('/'.$member->signature_path) }}" alt="" style="max-width:360px;max-height:140px;">
                        </div>
                    @endif
                </div>

                @if($member->isAwaiting() && ! $member->letter_id)
                    <div class="jb-card">
                        <form method="POST" action="{{ route('membership.approve', $member->id) }}" class="d-inline">
                            @csrf
                            <button class="jb-btn" type="submit" onclick="return confirm('Create a Letter of Admission and send it to the National President (signer)?');">
                                Approve — create Letter of Admission
                            </button>
                        </form>
                        <form method="POST" action="{{ route('membership.reject', $member->id) }}" class="mt-3">
                            @csrf
                            <label class="jb-label">Reject reason (optional)</label>
                            <textarea name="rejection_reason" class="jb-field" rows="2"></textarea>
                            <button class="btn btn-danger mt-2" type="submit" onclick="return confirm('Reject this membership?');">Reject</button>
                        </form>
                    </div>
                @elseif($member->isAwaiting() && $member->letter_id)
                    <div class="jb-card">
                        <p class="mb-0">Letter of Admission #{{ $member->letter_id }} is awaiting the National President signature in Letters → Awaiting Signature.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
