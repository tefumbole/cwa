@extends('beyond.layout')

@section('title', __('cwa.membership.thanks_title'))
@section('meta_description', __('cwa.membership.thanks_meta'))

@section('content')
<div class="max-w-xl mx-auto px-4 py-16 text-center">
    <div class="mx-auto w-16 h-16 rounded-full bg-brand-gold/20 flex items-center justify-center mb-5">
        <i data-lucide="check" class="w-8 h-8 text-brand-blue"></i>
    </div>
    <h1 class="text-2xl font-extrabold text-brand-blue mb-3">{{ __('cwa.membership.thanks_title') }}</h1>
    <p class="text-slate-600">{{ __('cwa.membership.thanks_body', ['name' => $name ?: 'sister']) }}</p>
    <a href="{{ url('/') }}" class="inline-flex mt-8 bg-brand-gold text-brand-blue font-bold rounded-full px-6 py-2.5">{{ __('cwa.nav.home') }}</a>
</div>
@endsection
