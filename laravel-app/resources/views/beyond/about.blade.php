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
        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-5">
            @foreach ($leaders as $leader)
                @php $flag = $leader->countryFlag(); @endphp
                <article class="leader-card rounded-2xl bg-white border border-stone-200/80 px-3 py-4 text-center min-w-0">
                    <div class="leader-ring">
                        @if ($leader->photoPublicUrl())
                            <div class="leader-avatar">
                                <img src="{{ $leader->photoPublicUrl() }}" alt="{{ $leader->name }}" width="640" height="640">
                            </div>
                        @else
                            <div class="leader-avatar leader-avatar-empty" aria-hidden="true">{{ mb_strtoupper(mb_substr($leader->name, 0, 1)) }}</div>
                        @endif
                    </div>
                    <h3 class="mt-3 mb-0 text-lg font-extrabold text-brand-blue leading-snug">{{ $leader->name }}@if ($flag) <span class="leader-flag" title="{{ $leader->country }}">{{ $flag }}</span>@endif</h3>
                    <p class="mt-1 mb-0 text-xs sm:text-sm font-extrabold tracking-[0.08em] uppercase text-brand-gold leading-tight">{{ $leader->title }}</p>
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
    .leader-ring {
        width: min(18rem, 100%);
        margin: 0 auto;
        padding: 0.55rem;
        border-radius: 999px;
        background: #003D82;
    }
    .leader-avatar {
        width: 100%;
        aspect-ratio: 1;
        border-radius: 999px;
        overflow: hidden;
        border: 0.55rem solid #D4AF37;
        background: #efeae0;
        box-sizing: border-box;
    }
    .leader-avatar img {
        display: block;
        width: 100%;
        height: 100%;
        max-width: none;
        object-fit: cover;
        object-position: center center;
    }
    .leader-avatar-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #003D82;
        font-weight: 800;
        font-size: 2.4rem;
    }
    .leader-flag {
        font-style: normal;
        font-weight: 400;
        letter-spacing: 0;
        text-transform: none;
    }
</style>
@endsection
