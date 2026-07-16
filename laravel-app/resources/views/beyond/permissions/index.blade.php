@extends('beyond.layout')

@section('title', 'Staff Permissions')
@section('meta_description', 'Company members can request time-off permissions.')

@section('content')
<div class="min-h-screen bg-gray-50 pb-20">
    <div class="bg-gradient-to-r from-brand-blue via-[#004e9a] to-brand-dark text-white py-16 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <h1 class="text-4xl md:text-5xl font-extrabold mb-4">Permissions</h1>
            <p class="text-lg text-blue-100">Company staff can request permission for a specific date and time range.</p>
        </div>
    </div>

    <div class="max-w-3xl mx-auto px-4 -mt-8">
        <div class="bg-white rounded-xl shadow-xl border border-gray-100 p-6 md:p-8">
            @if (! $user)
                <p class="text-gray-700 mb-4">You must log in as a <strong>company member</strong> to apply for permission.</p>
                <a href="{{ url('/login') }}" class="inline-flex bg-brand-blue text-white font-bold px-5 py-2.5 rounded-md">Log in</a>
            @elseif (! $otpOk)
                <p class="text-gray-700 mb-4">Please complete WhatsApp OTP verification, then return here.</p>
                <a href="{{ url('/otp-verification') }}" class="inline-flex bg-brand-blue text-white font-bold px-5 py-2.5 rounded-md">Verify OTP</a>
            @elseif (! $isMember)
                <p class="text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm">
                    Your account role (<strong>{{ $user->role }}</strong>) is not a company staff role. Only members can submit permission requests.
                </p>
            @else
                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
                        <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
                <p class="text-sm text-gray-500 mb-4">Signed in as <strong>{{ $user->name }}</strong> ({{ $user->role }})</p>
                <form method="POST" action="{{ route('beyond.permissions.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Your role in the company *</label>
                        <input required name="company_role" value="{{ old('company_role', $user->role) }}" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2" placeholder="e.g. Technician, Engineer, Admin">
                    </div>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-semibold text-gray-700">From (date & time) *</label>
                            <input required type="datetime-local" name="from_at" value="{{ old('from_at') }}" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-gray-700">To (date & time) *</label>
                            <input required type="datetime-local" name="to_at" value="{{ old('to_at') }}" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">WhatsApp number (optional override)</label>
                        <input name="phone" value="{{ old('phone', $user->phone) }}" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Reason *</label>
                        <textarea required name="reason" rows="4" class="w-full mt-1 rounded-md border border-gray-200 px-3 py-2">{{ old('reason') }}</textarea>
                    </div>
                    <button type="submit" class="w-full bg-brand-gold hover:bg-[#b5952f] text-brand-blue font-bold py-3 rounded-md">
                        Submit Permission Request
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
