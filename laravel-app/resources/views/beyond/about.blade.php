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

    @if (!empty($leaders) && $leaders->isNotEmpty())
        <section id="leadership" class="mt-12 sm:mt-16">
            <h2 class="text-[1.55rem] leading-tight sm:text-3xl font-extrabold text-brand-blue tracking-tight m-0">{{ \App\Support\SiteContent::text('about.leadership_heading', __('cwa.about.leadership_heading')) }}</h2>
            <p class="mt-2 text-slate-600">{{ \App\Support\SiteContent::text('about.leadership_subtext', __('cwa.about.leadership_subtext')) }}</p>
            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                @foreach ($leaders as $leader)
                    <article class="leader-card rounded-2xl bg-white border border-stone-200/80 shadow-sm p-5 text-center">
                        @if ($leader->photoPublicUrl())
                            <img src="{{ $leader->photoPublicUrl() }}" alt="{{ $leader->name }}" class="leader-photo">
                        @else
                            <div class="leader-photo leader-photo-empty" aria-hidden="true">{{ mb_strtoupper(mb_substr($leader->name, 0, 1)) }}</div>
                        @endif
                        <h3 class="mt-4 mb-0 text-lg font-extrabold text-brand-blue">{{ $leader->name }}</h3>
                        <p class="mt-1 mb-0 text-xs font-extrabold tracking-[0.08em] uppercase text-brand-gold">{{ $leader->title }}</p>
                        @if ($leader->country)
                            <p class="mt-2 mb-0 text-sm text-slate-600">{{ $leader->countryLabel() }}</p>
                        @endif
                        @if ($leader->description)
                            <p class="mt-3 mb-0 text-sm text-slate-600 leading-relaxed text-left">{{ $leader->description }}</p>
                        @endif
                        @if ($leader->email || $leader->phone)
                            <div class="mt-3 flex flex-col gap-1 text-sm">
                                @if ($leader->email)
                                    <a href="mailto:{{ $leader->email }}" class="text-brand-blue font-semibold break-all">{{ $leader->email }}</a>
                                @endif
                                @if ($leader->phone)
                                    <a href="tel:{{ preg_replace('/\s+/', '', $leader->phone) }}" class="text-brand-blue font-semibold">{{ \App\Support\WhatsAppPhone::display($leader->phone) }}</a>
                                @endif
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
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
    .leader-photo {
        width: 8.5rem;
        height: 8.5rem;
        margin: 0 auto;
        border-radius: 999px;
        object-fit: cover;
        border: 4px solid #D4AF37;
        background: #003D82;
    }
    .leader-photo-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #D4AF37;
        font-weight: 800;
        font-size: 2rem;
    }
</style>
@endsection
