@extends('beyond.layout')

@section('title', __('cwa.branches.title'))
@section('meta_description', __('cwa.branches.meta'))

@php
    $provinces = \App\Support\CwaBranches::cameroonProvinces();
    $diaspora = \App\Support\CwaBranches::diaspora();
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12 md:py-16" x-data="branchesPage()">
    <p class="text-xs font-extrabold tracking-[0.2em] uppercase text-brand-gold mb-3">{{ __('cwa.branches.kicker') }}</p>
    <h1 class="text-3xl md:text-4xl font-extrabold text-brand-blue tracking-tight">{{ __('cwa.branches.hero') }}</h1>
    <p class="mt-4 text-slate-600 leading-relaxed text-[1.05rem] max-w-3xl">{{ __('cwa.branches.intro') }}</p>

    <div class="mt-8 flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="inline-flex rounded-full bg-white border border-stone-200 p-1 shadow-sm">
            <button type="button" @click="tab = 'cameroon'"
                    :class="tab === 'cameroon' ? 'bg-brand-blue text-white' : 'text-slate-600 hover:text-brand-blue'"
                    class="px-4 py-2 rounded-full text-sm font-extrabold transition-colors">{{ __('cwa.branches.tab_cameroon') }}</button>
            <button type="button" @click="tab = 'diaspora'"
                    :class="tab === 'diaspora' ? 'bg-brand-blue text-white' : 'text-slate-600 hover:text-brand-blue'"
                    class="px-4 py-2 rounded-full text-sm font-extrabold transition-colors">{{ __('cwa.branches.tab_diaspora') }}</button>
        </div>
        <label class="relative flex-1">
            <span class="sr-only">{{ __('cwa.branches.search') }}</span>
            <input type="search" x-model="q" placeholder="{{ __('cwa.branches.search') }}"
                   class="w-full rounded-full border border-stone-200 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none focus:border-brand-gold focus:ring-4 focus:ring-[#D4AF37]/20">
        </label>
    </div>

    <div class="mt-8 space-y-5" x-show="tab === 'cameroon' || q.trim()" x-cloak>
        <template x-for="province in visibleProvinces()" :key="province.key">
            <section class="branch-card">
                <p class="branch-kicker" x-text="province.seat"></p>
                <h2 class="branch-title" x-text="province.title"></h2>
                <ul class="branch-grid">
                    <template x-for="(name, i) in visibleNames(province.dioceses)" :key="province.key + '-' + i + '-' + name">
                        <li class="branch-chip" x-text="name"></li>
                    </template>
                </ul>
            </section>
        </template>
        <p x-show="tab === 'cameroon' && visibleProvinces().length === 0" class="text-slate-500 text-sm" x-cloak>{{ __('cwa.branches.empty') }}</p>
    </div>

    <div class="mt-8 space-y-5" x-show="tab === 'diaspora' || q.trim()" x-cloak>
        <template x-for="group in visibleGroups()" :key="group.key">
            <section class="branch-card">
                <div class="flex items-start gap-3">
                    <div class="flex items-center gap-1.5 pt-0.5 shrink-0">
                        <template x-for="(code, fi) in (group.flags || [])" :key="group.key + '-flag-' + code">
                            <span class="branch-flag" :style="'animation-delay:' + (fi * 0.22) + 's'">
                                <img :src="'https://flagcdn.com/w80/' + code + '.png'"
                                     :srcset="'https://flagcdn.com/w40/' + code + '.png 1x, https://flagcdn.com/w80/' + code + '.png 2x'"
                                     :alt="(group.kicker || group.title) + ' flag'"
                                     width="40" height="27">
                            </span>
                        </template>
                    </div>
                    <div class="min-w-0">
                        <p class="branch-kicker" x-text="group.kicker || group.title"></p>
                        <h2 class="branch-title" x-text="group.title"></h2>
                    </div>
                </div>
                <template x-for="(zone, zi) in visibleZones(group)" :key="group.key + '-z-' + zi">
                    <div class="mt-5">
                        <p class="branch-kicker" x-show="zone.title" x-text="zone.title"></p>
                        <ul class="branch-grid" :class="zone.title ? '' : 'mt-0'">
                            <template x-for="(name, i) in visibleNames(zone.branches)" :key="group.key + '-' + zi + '-' + i + '-' + name">
                                <li class="branch-chip" x-text="name"></li>
                            </template>
                        </ul>
                    </div>
                </template>
            </section>
        </template>
        <p x-show="tab === 'diaspora' && visibleGroups().length === 0" class="text-slate-500 text-sm" x-cloak>{{ __('cwa.branches.empty') }}</p>
    </div>

    <div class="mt-12 rounded-2xl bg-brand-blue text-white p-6 md:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold m-0">{{ __('cwa.branches.cta_title') }}</h2>
            <p class="mt-1 mb-0 text-white/80 text-sm">{{ __('cwa.branches.cta_body') }}</p>
        </div>
        <a href="{{ route('beyond.membership') }}"
           class="inline-flex justify-center items-center rounded-full bg-brand-gold text-brand-blue font-extrabold px-5 py-2.5 hover:bg-[#c4a030] whitespace-nowrap">
            {{ __('cwa.nav.join') }}
        </a>
    </div>
</div>
<style>
    .branch-card {
        background: #fff;
        border: 1px solid rgba(231, 224, 212, 0.9);
        border-radius: 1rem;
        padding: 1.5rem 1.55rem 1.6rem;
        box-shadow: 0 8px 24px rgba(26, 31, 46, 0.04);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
    }
    .branch-card:hover {
        transform: translateY(-3px);
        border-color: #D4AF37;
        background: #fffdf6;
        box-shadow: 0 14px 32px rgba(0, 61, 130, 0.12);
    }
    .branch-kicker {
        margin: 0 0 0.25rem;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #D4AF37;
    }
    .branch-title {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 800;
        line-height: 1.3;
        color: #003D82;
    }
    .branch-grid {
        display: grid;
        gap: 0.5rem;
        margin: 1rem 0 0;
        padding: 0;
        list-style: none;
    }
    @media (min-width: 640px) {
        .branch-grid { grid-template-columns: 1fr 1fr; }
    }
    .branch-chip {
        display: block;
        margin: 0;
        padding: 0.7rem 0.9rem;
        border-radius: 0.9rem;
        background: #fbfaf7;
        border: 1px solid #ece6db;
        color: #1A1F2E;
        font-size: 0.92rem;
        font-weight: 600;
        line-height: 1.35;
    }
    .branch-flag {
        display: inline-block;
        width: 2.15rem;
        height: 1.45rem;
        overflow: hidden;
        border-radius: 3px;
        transform-origin: left center;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.22);
        animation: branch-flag-wave 2.1s ease-in-out infinite;
    }
    .branch-flag img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        animation: branch-flag-ripple 2.1s ease-in-out infinite;
    }
    @keyframes branch-flag-wave {
        0%, 100% { transform: rotate(-6deg) skewY(-3deg); }
        50% { transform: rotate(5deg) skewY(2deg); }
    }
    @keyframes branch-flag-ripple {
        0%, 100% { transform: scaleX(1); filter: brightness(1); }
        50% { transform: scaleX(1.06) translateX(1px); filter: brightness(1.08); }
    }
    @media (prefers-reduced-motion: reduce) {
        .branch-flag,
        .branch-flag img { animation: none; }
    }
</style>
@endsection

@push('scripts')
<script>
function branchesPage() {
    return {
        tab: 'cameroon',
        q: '',
        provinces: @json($provinces),
        diaspora: @json($diaspora),
        needle: function () {
            return String(this.q || '').toLowerCase().trim();
        },
        match: function (text) {
            var q = this.needle();
            if (!q) return true;
            return String(text || '').toLowerCase().indexOf(q) !== -1;
        },
        visibleNames: function (names) {
            var self = this;
            return (names || []).filter(function (n) { return self.match(n); });
        },
        visibleProvinces: function () {
            var self = this;
            return (this.provinces || []).filter(function (p) {
                if (self.match(p.title) || self.match(p.seat)) return true;
                return (p.dioceses || []).some(function (n) { return self.match(n); });
            });
        },
        visibleZones: function (group) {
            var self = this;
            return (group.zones || []).filter(function (z) {
                if (z.title && self.match(z.title)) return true;
                return (z.branches || []).some(function (n) { return self.match(n); });
            });
        },
        visibleGroups: function () {
            var self = this;
            return (this.diaspora || []).filter(function (g) {
                if (self.match(g.title) || self.match(g.kicker)) return true;
                return self.visibleZones(g).length > 0;
            });
        }
    };
}
</script>
@endpush
