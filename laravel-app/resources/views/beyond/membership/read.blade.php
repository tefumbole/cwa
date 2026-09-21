@extends('beyond.layout')

@section('title', $title)
@section('meta_description', $meta)

@php
    $kind = $kind ?? 'articles';
    $isBylaws = $kind === 'bylaws';
    $total = count($items);
@endphp

@section('content')
<div class="cwa-doc" x-data="{
        q: '',
        toc: false,
        active: 1,
        headings: {{ json_encode(array_column($items, 'heading')) }},
        hit: function (heading) {
            if (!this.q) return true;
            return String(heading).toLowerCase().indexOf(this.q.toLowerCase()) !== -1;
        },
        none: function () {
            var self = this;
            return this.headings.filter(function (h) { return self.hit(h); }).length === 0;
        },
        spy: function () {
            var self = this;
            var ticking = false;
            var update = function () {
                ticking = false;
                var cards = self.$el.querySelectorAll('.cwa-doc-card');
                if (!cards.length) return;
                var marker = 150;
                var current = 1;
                for (var i = 0; i < cards.length; i++) {
                    if (cards[i].offsetParent === null) continue;
                    if (cards[i].getBoundingClientRect().top <= marker) {
                        var id = cards[i].id || '';
                        var n = parseInt(id.replace('doc-', ''), 10);
                        if (n) current = n;
                    }
                }
                if (self.active !== current) {
                    self.active = current;
                    self.$nextTick(function () {
                        var link = self.$el.querySelector('.cwa-doc-toc a.is-active');
                        if (link && link.scrollIntoView) {
                            link.scrollIntoView({ block: 'nearest', inline: 'nearest' });
                        }
                    });
                }
            };
            window.addEventListener('scroll', function () {
                if (!ticking) {
                    ticking = true;
                    window.requestAnimationFrame(update);
                }
            }, { passive: true });
            update();
        }
    }" x-init="spy()">
    <a href="{{ route('beyond.membership', ['open' => 1]) }}" class="cwa-doc-float-close" title="{{ __('cwa.membership.close') }}">
        <i data-lucide="x" class="w-5 h-5"></i>
        <span>{{ __('cwa.membership.close') }}</span>
    </a>
    <div class="cwa-doc-top">
        <a href="{{ route('beyond.membership', ['open' => 1]) }}" class="cwa-doc-back">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            {{ __('cwa.membership.back') }}
        </a>
        <h1>{{ $title }}</h1>
        <p>{{ $hint }}</p>
        <div class="cwa-doc-tabs" role="tablist">
            <a href="{{ route('beyond.membership.articles') }}" class="{{ $isBylaws ? '' : 'is-on' }}">{{ __('cwa.membership.articles_btn') }}</a>
            <a href="{{ route('beyond.membership.bylaws') }}" class="{{ $isBylaws ? 'is-on' : '' }}">{{ __('cwa.membership.bylaws_btn') }}</a>
        </div>
    </div>

    <div class="cwa-doc-grid">
        <aside class="cwa-doc-aside">
            <button type="button" class="cwa-doc-toc-btn" @click="toc = !toc">
                <i data-lucide="list" class="w-4 h-4"></i>
                {{ __('cwa.membership.doc_contents') }}
                <span x-text="active + ' / {{ $total }}'"></span>
            </button>
            <div class="cwa-doc-toc" :class="{ 'is-open': toc }">
                <label class="sr-only" for="cwa-doc-search">{{ __('cwa.membership.doc_search') }}</label>
                <input id="cwa-doc-search" type="search" x-model="q" placeholder="{{ __('cwa.membership.doc_search') }}">
                <nav aria-label="{{ __('cwa.membership.doc_contents') }}">
                    @foreach ($items as $i => $item)
                        <a href="#doc-{{ $i + 1 }}"
                           class="cwa-doc-toc-link"
                           :class="{ 'is-active': active === {{ $i + 1 }} }"
                           @click="toc = false; active = {{ $i + 1 }}"
                           x-show="hit({{ json_encode($item['heading']) }})"
                           x-cloak>
                            <span>{{ $item['badge'] }}</span>
                            {{ $item['heading'] }}
                        </a>
                    @endforeach
                    <p class="cwa-doc-empty" x-show="none()" x-cloak>{{ __('cwa.membership.doc_empty') }}</p>
                </nav>
            </div>
        </aside>

        <div class="cwa-doc-main">
            @foreach ($items as $i => $item)
                <article class="cwa-doc-card" id="doc-{{ $i + 1 }}"
                         x-show="hit({{ json_encode($item['heading']) }})">
                    <header>
                        <span class="cwa-doc-num">{{ $item['badge'] }}</span>
                        <div>
                            <p>{{ __('cwa.membership.doc_of', ['current' => $i + 1, 'total' => $total]) }}</p>
                            <h2>{{ $item['heading'] }}</h2>
                        </div>
                    </header>
                    <div class="cwa-doc-body membership-article">
                        {!! $item['body_html'] !!}
                    </div>
                </article>
            @endforeach
            <p class="cwa-doc-empty" x-show="none()" x-cloak>{{ __('cwa.membership.doc_empty') }}</p>

            <div class="cwa-doc-cta">
                <a href="{{ route('beyond.membership.register') }}" class="cwa-doc-register">
                    <i data-lucide="heart" class="w-4 h-4"></i>
                    {{ __('cwa.membership.subscribe') }}
                </a>
                <a href="{{ route('beyond.membership', ['open' => 1]) }}" class="cwa-doc-close">{{ __('cwa.membership.close') }}</a>
            </div>
        </div>
    </div>
</div>
<style>
    html { scroll-padding-top: 5.75rem; }
    .cwa-doc { max-width: 72rem; margin: 0 auto; padding: 1.5rem 1rem 4rem; }
    .cwa-doc-top { text-align: center; margin-bottom: 1.75rem; }
    .cwa-doc-back {
        display: inline-flex; align-items: center; gap: 0.4rem;
        color: #003D82; font-weight: 700; font-size: 0.9rem; text-decoration: none;
        margin-bottom: 0.85rem;
    }
    .cwa-doc-back:hover { color: #D4AF37; }
    .cwa-doc-top h1 {
        margin: 0;
        font-size: clamp(1.85rem, 4vw, 2.6rem);
        font-weight: 800;
        color: #003D82;
        letter-spacing: -0.03em;
    }
    .cwa-doc-top > p {
        margin: 0.55rem auto 0;
        max-width: 36rem;
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.5;
    }
    .cwa-doc-tabs {
        display: inline-flex;
        margin-top: 1.15rem;
        padding: 0.25rem;
        background: #fff;
        border: 1px solid #e7e0d4;
        border-radius: 999px;
        box-shadow: 0 6px 18px rgba(26, 31, 46, 0.05);
    }
    .cwa-doc-tabs a {
        min-width: 8.5rem;
        padding: 0.55rem 1.15rem;
        border-radius: 999px;
        font-weight: 800;
        font-size: 0.92rem;
        text-decoration: none;
        color: #475569;
    }
    .cwa-doc-tabs a.is-on {
        background: #003D82;
        color: #fff;
    }
    .cwa-doc-grid {
        display: grid;
        gap: 1.5rem;
    }
    @media (min-width: 1024px) {
        .cwa-doc-grid { grid-template-columns: 17.5rem minmax(0, 1fr); align-items: start; }
        .cwa-doc-toc-btn { display: none; }
        .cwa-doc-toc { display: block !important; }
    }
    .cwa-doc-aside {
        position: sticky;
        top: 4.75rem;
        z-index: 20;
    }
    .cwa-doc-toc-btn {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.7rem 0.9rem;
        border-radius: 0.9rem;
        border: 1px solid #e7e0d4;
        background: #fff;
        font-weight: 800;
        color: #003D82;
    }
    .cwa-doc-toc-btn span { margin-left: auto; color: #94a3b8; font-size: 0.75rem; font-weight: 700; }
    .cwa-doc-toc {
        display: none;
        margin-top: 0.6rem;
        background: #fff;
        border: 1px solid #e7e0d4;
        border-radius: 1.1rem;
        padding: 0.85rem;
        box-shadow: 0 10px 28px rgba(26, 31, 46, 0.06);
        max-height: calc(100vh - 8rem);
        overflow: auto;
    }
    .cwa-doc-toc.is-open { display: block; }
    .cwa-doc-toc input {
        width: 100%;
        border: 1px solid #e7e0d4;
        border-radius: 0.7rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.88rem;
        margin-bottom: 0.55rem;
        outline: none;
    }
    .cwa-doc-toc input:focus { border-color: #D4AF37; }
    .cwa-doc-toc nav { display: flex; flex-direction: column; gap: 0.15rem; }
    .cwa-doc-toc a {
        display: flex;
        align-items: flex-start;
        gap: 0.55rem;
        padding: 0.42rem 0.45rem;
        border-radius: 0.55rem;
        text-decoration: none;
        color: #334155;
        font-size: 0.82rem;
        line-height: 1.35;
        font-weight: 600;
    }
    .cwa-doc-toc a:hover, .cwa-doc-card:target { }
    .cwa-doc-toc a:hover { background: #F6F3EC; color: #003D82; }
    .cwa-doc-toc a.is-active {
        background: #003D82;
        color: #fff;
    }
    .cwa-doc-toc a.is-active span { color: #D4AF37; }
    .cwa-doc-toc a span {
        flex-shrink: 0;
        min-width: 1.4rem;
        color: #D4AF37;
        font-weight: 800;
        font-size: 0.78rem;
        padding-top: 0.05rem;
    }
    .cwa-doc-card {
        background: #fff;
        border: 1px solid #e7e0d4;
        border-radius: 1.15rem;
        padding: 1.25rem 1.25rem 1.35rem;
        margin-bottom: 1rem;
        box-shadow: 0 8px 24px rgba(26, 31, 46, 0.04);
        scroll-margin-top: 5.75rem;
    }
    .cwa-doc-card:target {
        border-color: #D4AF37;
        box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.22);
    }
    .cwa-doc-card header {
        display: flex;
        gap: 0.9rem;
        align-items: flex-start;
        margin-bottom: 0.95rem;
        padding-bottom: 0.85rem;
        border-bottom: 1px solid #f0ebe1;
    }
    .cwa-doc-num {
        flex-shrink: 0;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.8rem;
        background: #003D82;
        color: #D4AF37;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
    }
    .cwa-doc-card header p {
        margin: 0 0 0.15rem;
        font-size: 0.68rem;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        font-weight: 800;
        color: #D4AF37;
    }
    .cwa-doc-card header h2 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 800;
        color: #003D82;
        line-height: 1.3;
    }
    .cwa-doc-body { color: #334155; }
    .membership-article .cwa-block + .cwa-block { margin-top: 1.05rem; padding-top: 0.9rem; border-top: 1px solid #f0ebe1; }
    .membership-article .cwa-section {
        margin: 0 0 0.55rem;
        color: #003D82;
        font-weight: 800;
        font-size: 0.92rem;
    }
    .membership-article .cwa-para,
    .membership-article .cwa-lead { margin: 0 0 0.5rem; color: #334155; line-height: 1.65; }
    .membership-article .cwa-points { margin: 0; padding-left: 1.35rem; list-style: decimal; }
    .membership-article .cwa-points > li {
        color: #334155;
        margin: 0 0 0.65rem;
        padding-left: 0.3rem;
        line-height: 1.6;
    }
    .membership-article .cwa-points > li::marker { color: #D4AF37; font-weight: 800; }
    .membership-article .cwa-letters,
    .membership-article .cwa-dashes { margin: 0.4rem 0 0; padding-left: 1.15rem; }
    .membership-article .cwa-letters { list-style: lower-alpha; }
    .membership-article .cwa-dashes { list-style: disc; }
    .membership-article .cwa-letters > li,
    .membership-article .cwa-dashes > li { color: #475569; margin: 0 0 0.3rem; line-height: 1.55; }
    .membership-article .cwa-letters > li::marker,
    .membership-article .cwa-dashes > li::marker { color: #D4AF37; }
    .membership-article strong { font-weight: 700; color: #1A1F2E; }
    .membership-article .cwa-motto { color: #b8922a; }
    .cwa-doc-empty { color: #94a3b8; font-size: 0.88rem; padding: 0.5rem; }
    .cwa-doc-cta {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 0.75rem;
        margin-top: 1.75rem;
    }
    .cwa-doc-register {
        display: inline-flex; align-items: center; gap: 0.4rem;
        min-height: 3rem; padding: 0.7rem 1.4rem;
        border-radius: 999px; background: #D4AF37; color: #003D82;
        font-weight: 800; text-decoration: none;
    }
    .cwa-doc-close {
        display: inline-flex; align-items: center;
        min-height: 3rem; padding: 0.7rem 1.4rem;
        border-radius: 999px; border: 1.5px solid #003D82; color: #003D82;
        font-weight: 800; text-decoration: none;
    }
    .sr-only {
        position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
        overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0;
    }
    .cwa-doc-float-close {
        position: fixed;
        top: 5.15rem;
        right: 0.85rem;
        z-index: 60;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        min-height: 2.6rem;
        padding: 0.45rem 0.95rem 0.45rem 0.7rem;
        border-radius: 999px;
        background: #003D82;
        color: #fff;
        font-weight: 800;
        font-size: 0.88rem;
        text-decoration: none;
        box-shadow: 0 10px 24px rgba(0, 40, 85, 0.28);
    }
    .cwa-doc-float-close:hover { background: #002855; color: #D4AF37; }
    @media (max-width: 640px) {
        .cwa-doc-float-close span { display: none; }
        .cwa-doc-float-close {
            width: 2.7rem;
            height: 2.7rem;
            min-height: 0;
            padding: 0;
            justify-content: center;
            right: 0.7rem;
        }
    }
</style>
@endsection
