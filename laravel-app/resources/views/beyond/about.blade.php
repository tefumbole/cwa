@extends('beyond.layout')

@section('title', __('cwa.about.title'))
@section('meta_description', __('cwa.about.meta'))

@php
    $vision = \App\Support\SiteContent::text('about.vision_text', __('cwa.about.vision_text'));
    $mission = \App\Support\SiteContent::text('about.mission_text', __('cwa.about.mission_text'));
    $story = \App\Support\SiteContent::text('about.story_text', __('cwa.about.story_text'));
@endphp

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12 md:py-16">
    <p class="text-xs font-extrabold tracking-[0.2em] uppercase text-brand-gold mb-3">{{ __('cwa.about.kicker') }}</p>
    <h1 class="text-3xl md:text-4xl font-extrabold text-brand-blue tracking-tight">{{ \App\Support\SiteContent::text('about.hero_title', __('cwa.about.hero_title')) }}</h1>
    <p class="mt-4 text-slate-600 leading-relaxed text-[1.05rem]">{{ $story }}</p>

    <div class="mt-10 space-y-5">
        <section class="rounded-2xl bg-white border border-stone-200/80 shadow-sm p-6 md:p-7">
            <h2 class="text-xl font-extrabold text-brand-blue">{{ \App\Support\SiteContent::text('about.vision_heading', __('cwa.about.vision_heading')) }}</h2>
            <p class="mt-2 text-slate-600 leading-relaxed mb-0">{{ $vision }}</p>
        </section>

        <section class="rounded-2xl bg-white border border-stone-200/80 shadow-sm p-6 md:p-7">
            <h2 class="text-xl font-extrabold text-brand-blue">{{ \App\Support\SiteContent::text('about.mission_heading', __('cwa.about.mission_heading')) }}</h2>
            <p class="mt-2 text-slate-600 leading-relaxed mb-0">{{ $mission }}</p>
        </section>

        <section class="rounded-2xl bg-white border border-stone-200/80 shadow-sm p-6 md:p-7">
            <h2 class="text-xl font-extrabold text-brand-blue">{{ \App\Support\SiteContent::text('about.motto_label', __('cwa.about.motto_label')) }}</h2>
            <p class="mt-2 text-slate-600 leading-relaxed mb-0">
                “{{ \App\Support\SiteContent::text('about.motto_text', __('cwa.about.motto_text')) }}”
                — {{ \App\Support\SiteContent::text('about.motto_ref', __('cwa.about.motto_ref')) }}
            </p>
        </section>

        <section class="rounded-2xl bg-white border border-stone-200/80 shadow-sm p-6 md:p-7">
            <h2 class="text-xl font-extrabold text-brand-blue">{{ \App\Support\SiteContent::text('about.patron_title', __('cwa.about.patron_title')) }}</h2>
            <p class="mt-2 text-slate-600 leading-relaxed mb-0">{{ __('cwa.about.patron_text', ['date' => \App\Support\SiteContent::text('about.patron_feast', __('cwa.about.patron_feast'))]) }}</p>
        </section>
    </div>
</div>
@endsection
