@extends('beyond.layout')
@section('title', 'Permission Submitted')
@section('content')
<div class="min-h-screen bg-gray-50 py-16 px-4">
    <div class="max-w-lg mx-auto bg-white rounded-xl shadow-lg border border-gray-100 p-8 text-center">
        <h1 class="text-2xl font-extrabold text-brand-blue mb-2">Request submitted</h1>
        <p class="text-gray-600 mb-4">
            @if($permission)
                Your permission request <strong>{{ $permission->reference_number }}</strong> is awaiting approval.
            @else
                Reference <strong>{{ $reference }}</strong>.
            @endif
        </p>
        <a href="{{ url('/permissions') }}" class="inline-block mt-4 text-brand-blue font-semibold hover:underline">Back</a>
    </div>
</div>
@endsection
