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
    <p class="about-kicker text-xs font-extrabold tracking-[0.2em] uppercase text-brand-gold mb-3">{{ __('cwa.about.kicker') }}</p>
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
    <section id="leadership" class="leader-section">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pb-12 sm:pb-16">
            <h2 class="text-xl sm:text-2xl font-extrabold text-brand-blue tracking-tight m-0">{{ \App\Support\SiteContent::text('about.leadership_heading', __('cwa.about.leadership_heading')) }}</h2>
            <div class="leader-grid">
                @foreach ($leaders as $leader)
                    @php $flag = $leader->countryFlag(); @endphp
                    <article class="leader-card">
                        <div class="leader-slot">
                            <div class="leader-portrait">
                                @if ($leader->photoPublicUrl())
                                    <div class="leader-portrait-clip">
                                        <img src="{{ $leader->photoPublicUrl() }}" alt="{{ $leader->name }}" width="640" height="640">
                                    </div>
                                @else
                                    <div class="leader-portrait-clip leader-portrait-empty" aria-hidden="true">{{ mb_strtoupper(mb_substr($leader->name, 0, 1)) }}</div>
                                @endif
                            </div>
                        </div>
                        <h3 class="mt-1 mb-0 text-lg font-extrabold text-brand-blue leading-snug">{{ $leader->name }}@if ($flag) <span class="leader-flag" title="{{ $leader->country }}">{{ $flag }}</span>@endif</h3>
                        <p class="mt-1 mb-0 text-xs sm:text-sm font-extrabold tracking-[0.08em] uppercase text-brand-gold leading-tight">{{ $leader->title }}</p>
                    </article>
                @endforeach
            </div>
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
    .leader-section {
        background: #FFFFFF;
        overflow: visible;
    }
    .leader-grid {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
        align-items: flex-start;
        gap: 0.15rem 0.2rem;
        margin-top: 0.5rem;
    }
    .leader-card {
        background: #FFFFFF;
        text-align: center;
        overflow: visible;
        flex: 0 0 15.5rem;
        width: 15.5rem;
        max-width: 100%;
        padding: 0 0 0.2rem;
    }
    .leader-slot {
        width: calc(100% - 2.4rem);
        margin: 0 auto;
        container-type: inline-size;
        overflow: visible;
    }
    .leader-portrait {
        width: 100%;
        aspect-ratio: 1;
        margin: 1.7rem auto 0.45rem;
        box-sizing: content-box;
        border-radius: 50%;
        border: calc(100cqi * 4.6 / 300) solid #003E7E;
        background: transparent;
        overflow: visible;
        box-shadow:
            0 0 0 calc(100cqi * 9 / 300) #D8B32D,
            0 0 12px 2px rgba(216,179,45,0.9),
            0 0 24px 8px rgba(216,179,45,0.55),
            0 0 42px 14px rgba(216,179,45,0.28);
    }
    .leader-portrait-clip {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        overflow: hidden;
    }
    .leader-portrait img {
        display: block;
        width: 100%;
        height: 100%;
        max-width: none;
        object-fit: cover;
        object-position: center center;
    }
    .leader-portrait-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #003E7E;
        font-weight: 800;
        font-size: 2.4rem;
        background: #efeae0;
    }
    .leader-flag {
        font-style: normal;
        font-weight: 400;
        letter-spacing: 0;
        text-transform: none;
    }
    @media (max-width: 700px) {
        .about-kicker { letter-spacing: 0.1em; line-height: 1.5; }
        .leader-section { overflow: hidden; }
        .leader-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.15rem 0.25rem;
        }
        .leader-card {
            width: auto;
            flex: none;
            max-width: none;
            padding: 0 0.1rem 0.15rem;
        }
        .leader-slot {
            width: 100%;
            padding: 0.35rem 0.45rem 0;
            box-sizing: border-box;
        }
        .leader-portrait {
            margin: 0.85rem auto 0.25rem;
            box-shadow:
                0 0 0 calc(100cqi * 9 / 300) #D8B32D,
                0 0 8px 1px rgba(216,179,45,0.8),
                0 0 14px 4px rgba(216,179,45,0.32);
        }
        .leader-card h3 { font-size: 0.92rem; }
        .leader-card p { font-size: 0.62rem; letter-spacing: 0.04em; }
    }
    @media (max-width: 340px) {
        .leader-grid { gap: 0.1rem; }
        .leader-slot { padding-left: 0.3rem; padding-right: 0.3rem; }
        .leader-card h3 { font-size: 0.82rem; }
    }
</style>
@endsection
