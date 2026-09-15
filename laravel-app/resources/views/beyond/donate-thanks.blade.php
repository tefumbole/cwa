@extends('beyond.layout')

@section('title', 'Thank you')
@section('meta_description', 'Thank you for your donation to CWACAM.')

@section('content')
<div class="min-h-[60vh] bg-slate-50 flex items-center">
    <div class="max-w-md mx-auto px-4 py-16 text-center">
        <h1 class="text-3xl font-extrabold text-brand-blue">Thank you</h1>
        <p class="mt-3 text-gray-600">Your donation of <strong>{{ number_format((float) session('donation_amount', 0), 0, '.', ' ') }} XAF</strong> was received.</p>
        <a href="{{ url('/') }}" class="inline-flex mt-8 px-6 py-3 rounded-full bg-brand-blue text-white font-bold">Home</a>
    </div>
</div>
@endsection
