@extends('beyond.layout')

@section('title', __('cwa.resources.title'))
@section('meta_description', __('cwa.resources.subtitle'))

@section('content')
<section class="bg-gradient-to-r from-brand-blue via-brand-light to-brand-blue py-16">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="uppercase tracking-[0.28em] text-brand-gold text-xs font-bold mb-3">{{ __('cwa.resources.kicker') }}</p>
        <h1 class="text-4xl md:text-5xl font-extrabold text-white">{{ \App\Support\SiteContent::text('resources.hero_title', __('cwa.resources.title')) }}</h1>
        <p class="mt-4 text-lg text-blue-100 max-w-2xl mx-auto">{{ \App\Support\SiteContent::text('resources.hero_subtitle', __('cwa.resources.subtitle')) }}</p>
    </div>
</section>

<section class="py-16 bg-slate-50 min-h-[50vh]">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-3 gap-6">
        @foreach ($files as $file)
            <article class="bg-white rounded-2xl border border-slate-100 p-6 flex flex-col shadow-sm">
                <div class="flex items-center justify-between">
                    <i data-lucide="file-text" class="w-10 h-10 text-brand-gold"></i>
                    <span class="text-xs font-bold px-2 py-1 rounded bg-slate-100 text-brand-blue">{{ $file['lang'] }}</span>
                </div>
                <h2 class="mt-4 text-lg font-bold text-brand-blue">{{ $file['title'] }}</h2>
                <p class="mt-2 text-sm text-slate-500 flex-1">{{ __('cwa.resources.pdf') }}</p>
                @if ($file['exists'])
                    <a href="{{ $file['url'] }}" target="_blank" rel="noopener"
                       class="mt-6 inline-flex items-center justify-center gap-2 bg-brand-gold text-brand-blue font-bold px-4 py-2.5 rounded-full hover:bg-yellow-400">
                        {{ __('cwa.resources.download') }}
                    </a>
                @else
                    <span class="mt-6 inline-flex items-center justify-center text-sm font-semibold text-slate-400">{{ __('cwa.resources.soon') }}</span>
                @endif
            </article>
        @endforeach
    </div>
</section>
@endsection
