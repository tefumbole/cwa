@extends('beyond.layout')

@section('title', __('cwa.about.title'))
@section('meta_description', __('cwa.about.meta'))

@php
    $vision = \App\Support\SiteContent::text('about.vision_text', __('cwa.about.vision_text'));
    $mission = \App\Support\SiteContent::text('about.mission_text', __('cwa.about.mission_text'));
    $story = \App\Support\SiteContent::text('about.story_text', __('cwa.about.story_text'));
@endphp

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8 sm:py-12 md:py-16">
    <p class="text-xs font-extrabold tracking-[0.2em] uppercase text-brand-gold mb-3">{{ __('cwa.about.kicker') }}</p>
    <h1 class="text-[1.85rem] leading-tight sm:text-3xl md:text-4xl font-extrabold text-brand-blue tracking-tight">{{ \App\Support\SiteContent::text('about.hero_title', __('cwa.about.hero_title')) }}</h1>
    <p class="mt-4 text-slate-600 leading-relaxed text-[1.05rem]">{{ $story }}</p>

    <div class="mt-10 space-y-5">
        <section class="about-card rounded-2xl bg-white border border-stone-200/80 shadow-sm p-5 sm:p-6 md:p-7">
            <h2 class="text-xl font-extrabold text-brand-blue">{{ \App\Support\SiteContent::text('about.vision_heading', __('cwa.about.vision_heading')) }}</h2>
            <p class="mt-2 text-slate-600 leading-relaxed mb-0">{{ $vision }}</p>
        </section>

        <section class="about-card rounded-2xl bg-white border border-stone-200/80 shadow-sm p-5 sm:p-6 md:p-7">
            <h2 class="text-xl font-extrabold text-brand-blue">{{ \App\Support\SiteContent::text('about.mission_heading', __('cwa.about.mission_heading')) }}</h2>
            <p class="mt-2 text-slate-600 leading-relaxed mb-0">{{ $mission }}</p>
        </section>

        <section class="about-card rounded-2xl bg-white border border-stone-200/80 shadow-sm p-5 sm:p-6 md:p-7">
            <h2 class="text-xl font-extrabold text-brand-blue">{{ \App\Support\SiteContent::text('about.motto_label', __('cwa.about.motto_label')) }}</h2>
            <p class="mt-2 text-slate-600 leading-relaxed mb-0">
                “{{ \App\Support\SiteContent::text('about.motto_text', __('cwa.about.motto_text')) }}”
                — {{ \App\Support\SiteContent::text('about.motto_ref', __('cwa.about.motto_ref')) }}
            </p>
        </section>
    </div>
</div>

@if (!empty($leaders) && $leaders->isNotEmpty())
    <section id="leadership" class="max-w-6xl mx-auto px-3 sm:px-4 pb-10 sm:pb-14">
        <h2 class="text-xl sm:text-2xl font-extrabold text-brand-blue tracking-tight m-0">{{ \App\Support\SiteContent::text('about.leadership_heading', __('cwa.about.leadership_heading')) }}</h2>
        <div class="mt-4 grid grid-cols-2 min-[520px]:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 sm:gap-3">
            @foreach ($leaders as $leader)
                @php $flag = $leader->countryFlag(); @endphp
                <article class="leader-card rounded-xl bg-white border border-stone-200/80 px-2 py-3 text-center min-w-0">
                    @if ($leader->photoPublicUrl())
                        <div class="leader-avatar">
                            <img src="{{ $leader->photoPublicUrl() }}" alt="{{ $leader->name }}" width="240" height="240">
                        </div>
                    @else
                        <div class="leader-avatar leader-avatar-empty" aria-hidden="true">{{ mb_strtoupper(mb_substr($leader->name, 0, 1)) }}</div>
                    @endif
                    <h3 class="mt-2 mb-0 text-[0.92rem] sm:text-sm font-extrabold text-brand-blue leading-snug">{{ $leader->name }}@if ($flag) <span class="leader-flag" title="{{ $leader->country }}">{{ $flag }}</span>@endif</h3>
                    <p class="mt-0.5 mb-0 text-[0.65rem] sm:text-xs font-extrabold tracking-[0.06em] uppercase text-brand-gold leading-tight">{{ $leader->title }}</p>
                </article>
            @endforeach
        </div>
    </section>
@endif
<style>
    .about-card {
        cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
    }
    @media (hover: hover) {
        .about-card:hover {
            transform: translateY(-3px);
            border-color: #D4AF37;
            background: #fffdf6;
            box-shadow: 0 14px 32px rgba(0, 61, 130, 0.12);
        }
        .about-card:hover h2 { color: #002855; }
    }
    .leader-avatar {
        width: 4.75rem;
        height: 4.75rem;
        margin: 0 auto;
        border-radius: 999px;
        overflow: hidden;
        border: 2px solid #D4AF37;
        background: #efeae0;
    }
    .leader-avatar img {
        display: block;
        width: 100%;
        height: 100%;
        max-width: none;
        object-fit: cover;
        object-position: center 18%;
    }
    .leader-avatar-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #003D82;
        font-weight: 800;
        font-size: 1.35rem;
    }
    .leader-flag {
        font-style: normal;
        font-weight: 400;
        letter-spacing: 0;
        text-transform: none;
    }
</style>
@endsection
