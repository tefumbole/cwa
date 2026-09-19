@extends('beyond.layout')

@section('title', __('cwa.membership.page_title'))
@section('meta_description', __('cwa.membership.page_meta'))

@section('content')
<div class="bg-brand-blue text-white" style="min-height: calc(100vh - 5rem);">
    <div class="max-w-3xl mx-auto px-4 py-14 md:py-20 text-center">
        <p class="text-brand-gold text-xs font-bold uppercase tracking-widest mb-3">{{ __('cwa.join.kicker') }}</p>
        <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight mb-3">{{ __('cwa.membership.page_title') }}</h1>
        <p class="text-blue-100 text-base md:text-lg max-w-xl mx-auto mb-10">{{ __('cwa.membership.page_meta') }}</p>

        <a href="{{ route('beyond.membership.register') }}"
           class="inline-flex items-center justify-center gap-2 w-full sm:w-auto min-h-[3.25rem] px-10 py-3.5 rounded-full bg-brand-gold text-brand-blue font-extrabold text-lg shadow-lg shadow-brand-gold/25 hover:bg-[#b5952f]">
            <i data-lucide="heart" class="w-5 h-5"></i>
            {{ __('cwa.membership.subscribe') }}
        </a>

        <div class="mt-6">
            <a href="{{ route('beyond.membership.bylaws') }}"
               class="inline-flex items-center justify-center gap-2 min-h-[2.75rem] px-6 py-2.5 rounded-full border border-white/40 text-white font-semibold hover:border-brand-gold hover:text-brand-gold">
                <i data-lucide="book-open" class="w-4 h-4"></i>
                {{ __('cwa.membership.read_bylaws') }}
            </a>
        </div>

        <p class="mt-8 text-sm text-blue-200/90 max-w-md mx-auto mb-0">{{ __('cwa.membership.intro') }}</p>
    </div>
</div>
@endsection
