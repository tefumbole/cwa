@extends('beyond.layout')

@section('title', __('cwa.home.title'))
@section('meta_description', __('cwa.home.meta'))

@php
    $navLinks = \App\Support\SiteMenu::landingNavLinks();
    $currentUrl = url()->current();
    $logoMark = url('public/branding/cwa-logo-mary.png') . '?v=mary3';
@endphp

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;1,600;1,700&family=Great+Vibes&family=Playfair+Display:ital,wght@0,700;1,600;1,700&display=swap" rel="stylesheet">
<style>
    body > header.site-header,
    body > header.bg-brand-blue,
    body > a.cwa-wa { display: none !important; }
    body { background: #003D82; }
    main.flex-1 { display: flex; flex-direction: column; min-height: 100vh; }
    .lp {
        --gold: #d4af37;
        --gold-soft: #e8c96a;
        --navy: #071a38;
        --ink: #0a1c3d;
        --muted: rgba(255, 255, 255, 0.86);
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        color: #fff;
    }
    .lp-nav {
        position: relative;
        z-index: 30;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-height: 5.15rem;
        padding: 0.7rem 1.6rem 0.7rem 1.15rem;
        background: #fff;
        box-shadow: 0 8px 24px rgba(7, 26, 56, 0.08);
    }
    .lp-brand {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        text-decoration: none;
        min-width: 0;
        flex-shrink: 0;
    }
    .lp-brand img {
        width: 3.35rem;
        height: 3.35rem;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
    }
    .lp-brand-name {
        display: block;
        font-weight: 800;
        letter-spacing: 0.08em;
        color: #0a1c3d;
        font-size: 1.22rem;
        line-height: 1;
    }
    .lp-brand-tag {
        display: block;
        margin-top: 0.22rem;
        color: #6b7280;
        font-size: 0.52rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        line-height: 1.25;
        max-width: 11.5rem;
    }
    .lp-links {
        display: none;
        align-items: center;
        justify-content: center;
        gap: 2.4rem;
        flex: 1;
    }
    .lp-links a {
        color: #1f2a44;
        font-size: 1.15rem;
        font-weight: 600;
        letter-spacing: 0.01em;
        text-decoration: none;
        white-space: nowrap;
        padding: 0.25rem 0.15rem;
        border-bottom: 2px solid transparent;
    }
    .lp-links a:hover,
    .lp-links a.is-active {
        color: var(--gold);
        border-bottom-color: var(--gold);
    }
    .lp-actions {
        display: none;
        align-items: center;
        gap: 0.85rem;
        flex-shrink: 0;
    }
    .lp-search {
        width: 2.4rem;
        height: 2.4rem;
        border: 0;
        background: transparent;
        color: #1f2a44;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .lp-search:hover { color: var(--gold); }
    .lp-support {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 1.05rem;
        border: 1.5px solid var(--gold);
        border-radius: 999px;
        color: #8a6d1d;
        font-size: 0.82rem;
        font-weight: 700;
        text-decoration: none;
        background: #fff;
        white-space: nowrap;
    }
    .lp-support:hover { background: #fff8e8; }
    .lp-join {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 1.05rem;
        border-radius: 999px;
        background: var(--gold);
        color: #071a38;
        font-size: 0.82rem;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }
    .lp-join:hover { background: #e8c96a; }
    .lp-menu-btn {
        width: 2.5rem;
        height: 2.5rem;
        border: 0;
        background: transparent;
        color: #0a1c3d;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .lp-drawer {
        display: none;
        background: #fff;
        border-top: 1px solid #eee;
        padding: 0.75rem 1.15rem 1.1rem;
    }
    .lp-drawer.is-open { display: block; }
    .lp-drawer a {
        display: block;
        padding: 0.7rem 0;
        color: #1f2a44;
        font-size: 1.12rem;
        font-weight: 600;
        text-decoration: none;
        border-bottom: 1px solid #f3f4f6;
    }
    .lp-search-bar {
        display: none;
        padding: 0.65rem 1.15rem 0.9rem;
        background: #fff;
        border-top: 1px solid #eee;
    }
    .lp-search-bar.is-open { display: block; }
    .lp-search-bar input {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 999px;
        padding: 0.55rem 1rem;
        font-size: 0.9rem;
        outline: none;
    }
    .lp-hero {
        position: relative;
        flex: 1 1 auto;
        min-height: calc(100vh - 5.15rem);
        display: flex;
        flex-direction: column;
        justify-content: center;
        overflow: hidden;
        padding-bottom: 2.85rem;
        background: #003D82 center right / cover no-repeat;
        background-image: url('{{ $heroImageFallback ?? $heroImage }}');
    }
    @supports (background-image: url('x.webp')) {
        .lp-hero { background-image: url('{{ $heroImage }}'); }
    }
    .lp-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
            linear-gradient(90deg,
                rgba(0, 61, 130, 0.96) 0%,
                rgba(0, 40, 85, 0.88) 24%,
                rgba(0, 61, 130, 0.42) 46%,
                rgba(0, 61, 130, 0.08) 62%,
                transparent 74%),
            linear-gradient(180deg, rgba(0, 40, 85, 0.18) 0%, transparent 22%, rgba(0, 61, 130, 0.45) 78%, rgba(0, 61, 130, 0.96) 100%);
        pointer-events: none;
    }
    .lp-copy {
        position: relative;
        z-index: 2;
        width: min(640px, 92vw);
        padding: 2.4rem 0 1.4rem 3.2rem;
    }
    .lp-kicker {
        letter-spacing: 0.34em;
        text-transform: uppercase;
        font-size: 0.68rem;
        font-weight: 700;
        color: rgba(255, 255, 255, 0.78);
        margin-bottom: 0.85rem;
    }
    .lp-copy h1 {
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(3.1rem, 6.2vw, 5.15rem);
        line-height: 0.92;
        font-weight: 700;
        color: #fff;
        letter-spacing: -0.02em;
    }
    .lp-copy h1 em {
        display: block;
        margin-top: 0.35rem;
        font-family: "Cormorant Garamond", "Playfair Display", Georgia, serif;
        font-style: italic;
        font-weight: 600;
        color: var(--gold-soft);
        font-size: 0.46em;
        letter-spacing: 0;
        line-height: 1.15;
    }
    .lp-sub {
        margin-top: 1.05rem;
        max-width: 34rem;
        color: var(--muted);
        font-size: 0.98rem;
        line-height: 1.55;
    }
    .lp-quote {
        margin-top: 1.05rem;
        padding-left: 0.9rem;
        border-left: 2px solid var(--gold);
        color: rgba(255, 255, 255, 0.88);
        font-style: italic;
        font-size: 0.92rem;
        line-height: 1.45;
        max-width: 28rem;
    }
    .lp-quote cite {
        display: block;
        margin-top: 0.15rem;
        font-style: normal;
        font-size: 0.82rem;
        color: rgba(255, 255, 255, 0.7);
    }
    .lp-btns {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.35rem;
    }
    .lp-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.72rem 1.15rem;
        border-radius: 999px;
        font-size: 0.88rem;
        font-weight: 700;
        text-decoration: none;
        line-height: 1;
    }
    .lp-btn.gold {
        background: var(--gold);
        color: #3b2a08;
        box-shadow: 0 8px 18px rgba(212, 175, 55, 0.28);
    }
    .lp-btn.gold:hover { background: #e0c05a; }
    .lp-btn.ghost {
        background: transparent;
        color: #fff;
        border: 1.5px solid rgba(255, 255, 255, 0.55);
    }
    .lp-btn.ghost:hover { border-color: var(--gold); color: var(--gold-soft); }
    .lp-meter {
        display: flex;
        align-items: center;
        gap: 1.1rem;
        margin-top: 1.7rem;
        padding: 0.85rem 1.15rem 0.95rem;
        width: max-content;
        max-width: calc(100vw - 3rem);
        border-radius: 1.35rem;
        background: rgba(6, 18, 42, 0.72);
        border: 1px solid rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    .lp-rings {
        display: flex;
        align-items: center;
        gap: 0.7rem;
    }
    .unit { text-align: center; }
    .dial { position: relative; width: 74px; height: 74px; }
    .dial svg { width: 74px; height: 74px; transform: rotate(-90deg); }
    .dial .track { fill: none; stroke: rgba(255,255,255,0.16); stroke-width: 6; }
    .dial .progress {
        fill: none;
        stroke: var(--gold);
        stroke-width: 6;
        stroke-linecap: round;
        filter: drop-shadow(0 0 6px rgba(212, 175, 55, 0.45));
        transition: stroke-dashoffset 0.35s linear;
    }
    .dial-inner {
        position: absolute;
        inset: 9px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .dial-value {
        font-family: "Playfair Display", Georgia, serif;
        font-size: 1.28rem;
        font-weight: 700;
        line-height: 1;
        color: #fff;
    }
    .dial-label {
        margin-top: 0.12rem;
        font-size: 0.48rem;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--gold-soft);
        font-weight: 700;
    }
    .lp-launch {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        padding-left: 1rem;
        border-left: 1px solid rgba(255, 255, 255, 0.16);
        color: #fff;
        min-width: 9.5rem;
    }
    .lp-launch i { color: var(--gold-soft); }
    .lp-launch small {
        display: block;
        font-size: 0.58rem;
        letter-spacing: 0.16em;
        font-weight: 700;
        color: var(--gold-soft);
    }
    .lp-launch strong {
        display: block;
        margin-top: 0.12rem;
        font-size: 0.82rem;
        letter-spacing: 0.04em;
        font-weight: 800;
        text-transform: uppercase;
    }
    .lp-credo {
        position: absolute;
        right: 4.2%;
        top: 13%;
        z-index: 2;
        width: min(360px, 38vw);
        text-align: left;
        color: #fff;
        pointer-events: none;
        padding: 1.15rem 1.25rem 1.25rem;
        border-radius: 18px;
        background: rgba(4, 16, 40, 0.38);
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        box-shadow: 0 16px 40px rgba(4, 16, 40, 0.25);
    }
    .lp-credo h2 {
        font-family: "Playfair Display", Georgia, serif;
        font-size: clamp(1.45rem, 2vw, 1.85rem);
        font-weight: 700;
        line-height: 1.15;
        margin: 0 0 0.85rem;
        text-shadow: 0 6px 18px rgba(4, 16, 40, 0.35);
    }
    .lp-credo .label {
        margin: 0.7rem 0 0.22rem;
        color: var(--gold-soft);
        letter-spacing: 0.16em;
        text-transform: uppercase;
        font-size: 0.62rem;
        font-weight: 800;
    }
    .lp-credo p {
        margin: 0;
        font-size: 0.82rem;
        line-height: 1.45;
        color: rgba(255, 255, 255, 0.92);
        text-shadow: 0 4px 14px rgba(4, 16, 40, 0.35);
    }
    .lp-verse {
        position: absolute;
        right: 5.5%;
        bottom: 7.5%;
        z-index: 2;
        max-width: 16rem;
        text-align: right;
        color: rgba(255, 255, 255, 0.92);
        font-family: "Cormorant Garamond", Georgia, serif;
        font-style: italic;
        font-size: 0.98rem;
        line-height: 1.4;
        text-shadow: 0 6px 16px rgba(4, 16, 40, 0.4);
        pointer-events: none;
    }
    .lp-verse span {
        display: block;
        margin-top: 0.2rem;
        font-style: normal;
        font-size: 0.78rem;
        letter-spacing: 0.04em;
    }
    .launched { display: none; color: var(--gold-soft); font-family: "Playfair Display", Georgia, serif; font-size: 1.25rem; }
    .launched.is-visible { display: block; }
    .lp-rings.is-hidden { display: none; }
    @media (min-width: 1024px) {
        .lp-links, .lp-actions { display: flex; }
        .lp-menu-btn { display: none; }
    }
    @media (max-width: 1023px) {
        .lp-copy { padding: 1.6rem 1.2rem 1.8rem; width: 100%; }
        .lp-credo, .lp-verse { display: none; }
        .lp-meter { width: 100%; max-width: 100%; flex-wrap: wrap; }
        .lp-launch { border-left: 0; padding-left: 0; }
        .lp-hero { background-position: 62% 20%; }
        .lp-hero::before {
            background: linear-gradient(180deg, rgba(0,40,85,0.2) 0%, rgba(0,61,130,0.55) 42%, rgba(0,61,130,0.96) 100%);
        }
    }
    @media (max-width: 640px) {
        .lp-brand-tag { display: none; }
        .dial, .dial svg { width: 62px; height: 62px; }
        .dial-inner { inset: 8px; }
        .dial-value { font-size: 1.05rem; }
        .lp-rings { gap: 0.35rem; }
        .lp-copy h1 { font-size: 3rem; }
    }
</style>
@endpush

@section('content')
<div class="lp" x-data="{ open: false, search: false }">
    <header class="lp-nav">
        <a href="{{ url('/') }}" class="lp-brand" aria-label="{{ __('cwa.nav.home') }}">
            <img src="{{ $logoMark }}" alt="CWACAM">
            <span>
                <span class="lp-brand-name">CWACAM</span>
                <span class="lp-brand-tag">{{ __('cwa.home.brand_tag') }}</span>
            </span>
        </a>

        <nav class="lp-links" aria-label="Primary">
            @foreach ($navLinks as $link)
                @php $active = \App\Support\SiteMenu::navLinkIsActive($link, $currentUrl); @endphp
                <a href="{{ $link['url'] }}" class="{{ $active ? 'is-active' : '' }}">{{ $link['label'] }}</a>
            @endforeach
        </nav>

        <div class="lp-actions">
            <button type="button" class="lp-search" @click="search = !search; open = false" aria-label="{{ __('cwa.nav.search') }}">
                <i data-lucide="search" class="w-5 h-5"></i>
            </button>
            @include('beyond.partials.lang_switch', ['variant' => 'light'])
            <a href="{{ route('beyond.donate') }}" class="lp-support">
                <i data-lucide="heart" class="w-4 h-4"></i>
                {{ __('cwa.nav.donate') }}
            </a>
            <a href="{{ route('beyond.membership') }}" class="lp-join">{{ __('cwa.nav.join') }}</a>
        </div>

        <button type="button" class="lp-menu-btn" @click="open = !open; search = false" aria-label="{{ __('cwa.nav.menu') }}">
            <i data-lucide="menu" class="w-6 h-6" x-show="!open"></i>
            <i data-lucide="x" class="w-6 h-6" x-show="open" x-cloak></i>
        </button>
    </header>

    <div class="lp-search-bar" :class="{ 'is-open': search }" x-cloak>
        <form action="{{ url('/calendar') }}" method="get">
            <input type="search" name="q" placeholder="{{ __('cwa.home.search_placeholder') }}" aria-label="{{ __('cwa.nav.search') }}">
        </form>
    </div>
    <div class="lp-drawer" :class="{ 'is-open': open }" x-cloak>
        @foreach ($navLinks as $link)
            <a href="{{ $link['url'] }}">{{ $link['label'] }}</a>
        @endforeach
        <a href="{{ route('beyond.membership') }}">{{ __('cwa.nav.join') }}</a>
        <a href="{{ route('beyond.donate') }}">{{ __('cwa.nav.donate') }}</a>
        <a href="{{ url('/documents') }}">{{ __('cwa.nav.resources') }}</a>
        <div class="pt-2">@include('beyond.partials.lang_switch', ['variant' => 'light'])</div>
        <a href="{{ url('/login') }}">{{ __('cwa.nav.login') }}</a>
    </div>

    <section class="lp-hero" aria-label="{{ __('cwa.home.hero_aria') }}">
        <div class="lp-copy">
            <p class="lp-kicker">{{ __('cwa.home.kicker') }}</p>
            <h1>{{ __('cwa.home.headline') }}<em>{{ __('cwa.home.headline_em') }}</em></h1>
            <p class="lp-sub">{!! __('cwa.home.sub') !!}</p>
            <blockquote class="lp-quote">
                “{{ __('cwa.home.quote') }}”
                <cite>– CWACAM</cite>
            </blockquote>
            <div class="lp-btns">
                <a class="lp-btn gold" href="{{ route('beyond.membership') }}">
                    <i data-lucide="heart" class="w-4 h-4"></i>
                    {{ __('cwa.nav.join') }} →
                </a>
            </div>

            <div class="lp-meter">
                <div class="lp-rings" id="rings" data-target="{{ $launchAtIso }}" data-window-days="{{ $windowDays }}">
                    @foreach (['days' => __('cwa.home.days'), 'hours' => __('cwa.home.hours'), 'mins' => __('cwa.home.mins'), 'secs' => __('cwa.home.secs')] as $id => $label)
                        <div class="unit">
                            <div class="dial">
                                <svg viewBox="0 0 120 120" aria-hidden="true">
                                    <circle class="track" cx="60" cy="60" r="52"></circle>
                                    <circle class="progress" id="ring-{{ $id }}" cx="60" cy="60" r="52"></circle>
                                </svg>
                                <div class="dial-inner">
                                    <span class="dial-value" id="val-{{ $id }}">--</span>
                                    <span class="dial-label">{{ $label }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="launched" id="launched">{{ __('cwa.home.launched') }}</p>
                <div class="lp-launch">
                    <i data-lucide="calendar" class="w-5 h-5"></i>
                    <span>
                        <small>{{ __('cwa.home.launch') }}</small>
                        <strong>{{ strtoupper($launchLabel ?? '15 October 2026') }}</strong>
                    </span>
                </div>
            </div>
        </div>

        <div class="lp-credo">
            <h2>{{ __('cwa.home.credo_title') }}</h2>
            <p class="label">{{ __('cwa.home.vision_label') }}</p>
            <p>{{ __('cwa.about.vision_text') }}</p>
            <p class="label">{{ __('cwa.home.mission_label') }}</p>
            <p>{{ __('cwa.about.mission_text') }}</p>
        </div>
        <p class="lp-verse">“{{ __('cwa.home.verse') }}”<span>{{ __('cwa.home.verse_ref') }}</span></p>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var C = 2 * Math.PI * 52;
    var rings = document.getElementById('rings');
    if (!rings) return;
    var launched = document.getElementById('launched');
    var target = new Date(rings.getAttribute('data-target')).getTime();
    var windowDays = Math.max(1, parseInt(rings.getAttribute('data-window-days'), 10) || 31);
    var nodes = {
        days: { val: document.getElementById('val-days'), ring: document.getElementById('ring-days') },
        hours: { val: document.getElementById('val-hours'), ring: document.getElementById('ring-hours') },
        mins: { val: document.getElementById('val-mins'), ring: document.getElementById('ring-mins') },
        secs: { val: document.getElementById('val-secs'), ring: document.getElementById('ring-secs') }
    };
    Object.keys(nodes).forEach(function (key) {
        nodes[key].ring.style.strokeDasharray = String(C);
        nodes[key].ring.style.strokeDashoffset = String(C);
    });
    function setRing(node, value, max) {
        var progress = Math.max(0, Math.min(1, value / max));
        node.val.textContent = String(value);
        node.ring.style.strokeDashoffset = String(C * (1 - progress));
    }
    function tick() {
        var diff = target - Date.now();
        if (diff <= 0) {
            rings.classList.add('is-hidden');
            launched.classList.add('is-visible');
            return false;
        }
        var secs = Math.floor(diff / 1000);
        var days = Math.floor(secs / 86400); secs %= 86400;
        var hours = Math.floor(secs / 3600); secs %= 3600;
        var mins = Math.floor(secs / 60); secs %= 60;
        setRing(nodes.days, days, windowDays);
        setRing(nodes.hours, hours, 24);
        setRing(nodes.mins, mins, 60);
        setRing(nodes.secs, secs, 60);
        return true;
    }
    if (tick()) setInterval(tick, 1000);
})();
</script>
@endpush
