@extends('beyond.layout')

@section('title', __('cwa.donate.thanks_title'))
@section('meta_description', __('cwa.donate.thanks_meta'))

@section('content')
<div class="min-h-[60vh] bg-slate-50 flex items-center">
    <div class="max-w-md mx-auto px-4 py-16 text-center">
        <h1 class="text-3xl font-extrabold text-brand-blue">{{ __('cwa.donate.thanks_title') }}</h1>
        <p class="mt-3 text-gray-600">{{ __('cwa.donate.thanks_body', ['amount' => number_format((float) session('donation_amount', 0), 0, '.', ' ')]) }}</p>
        <a href="{{ url('/') }}" class="inline-flex mt-8 px-6 py-3 rounded-full bg-brand-blue text-white font-bold">{{ __('cwa.nav.home') }}</a>
    </div>
</div>
@endsection
