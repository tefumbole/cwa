@extends('beyond.layout')

@section('title', __('cwa.branches.title'))
@section('meta_description', __('cwa.branches.meta'))

@php
    $provinces = \App\Support\CwaBranches::cameroonProvinces();
    $diaspora = \App\Support\CwaBranches::diaspora();
@endphp

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8 sm:py-12 md:py-16" x-data="branchesPage()">
    <p class="text-xs font-extrabold tracking-[0.2em] uppercase text-brand-gold mb-3">{{ __('cwa.branches.kicker') }}</p>
    <h1 class="text-[1.7rem] leading-tight sm:text-3xl md:text-4xl font-extrabold text-brand-blue tracking-tight">{{ __('cwa.branches.hero') }}</h1>
    <p class="mt-4 text-slate-600 leading-relaxed text-[0.98rem] sm:text-[1.05rem] max-w-3xl">{{ __('cwa.branches.intro') }}</p>

    <div class="mt-6 sm:mt-8 flex flex-col gap-3">
        <div class="flex items-center gap-2 sm:gap-3">
            <button type="button" @click="tab = 'cameroon'"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center min-h-[2.75rem] px-5 sm:px-7 py-2.5 rounded-full border-2 border-[#003D82] font-extrabold text-[#003D82]"
                    :class="tab === 'cameroon' ? 'bg-[#003D82] text-white' : 'bg-white hover:bg-[#003D82] hover:text-white'">
                {{ __('cwa.branches.tab_cameroon') }}
            </button>
            <button type="button" @click="tab = 'diaspora'"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center min-h-[2.75rem] px-5 sm:px-7 py-2.5 rounded-full border-2 border-[#D4AF37] font-extrabold text-[#003D82]"
                    :class="tab === 'diaspora' ? 'bg-[#D4AF37] text-[#003D82]' : 'bg-white hover:bg-[#D4AF37]'">
                {{ __('cwa.branches.tab_diaspora') }}
            </button>
        </div>
        <label class="relative w-full">
            <span class="sr-only">{{ __('cwa.branches.search') }}</span>
            <input type="search" x-model="q" placeholder="{{ __('cwa.branches.search') }}"
                   class="w-full rounded-full border border-stone-200 bg-white px-4 py-2.5 text-base text-slate-700 shadow-sm outline-none focus:border-brand-gold focus:ring-4 focus:ring-[#D4AF37]/20">
        </label>
    </div>

    <div class="mt-8 space-y-5 max-w-4xl" x-show="tab === 'cameroon'" x-cloak>
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

    <div class="mt-8 diaspora-shell" x-show="tab === 'diaspora'" x-cloak>
        <aside class="diaspora-nav" aria-label="{{ __('cwa.branches.tab_diaspora') }}">
            <template x-for="group in visibleGroups()" :key="'nav-'+group.key">
                <button type="button" class="diaspora-link" :class="country === group.key ? 'is-on' : ''"
                        @click="country = group.key">
                    <template x-for="code in (group.flags || [])" :key="'navflag-'+group.key+'-'+code">
                        <span class="branch-flag is-sm">
                            <img :src="'https://flagcdn.com/w40/' + code + '.png'"
                                 :alt="group.kicker || group.title" width="28" height="19">
                        </span>
                    </template>
                    <span x-text="group.kicker || group.title"></span>
                </button>
            </template>
        </aside>
        <div class="diaspora-main">
            <template x-if="activeGroup()">
                <section class="branch-card">
                    <div class="flex items-start gap-3">
                        <template x-for="code in (activeGroup().flags || [])" :key="'mainflag-'+activeGroup().key+'-'+code">
                            <span class="branch-flag">
                                <img :src="'https://flagcdn.com/w80/' + code + '.png'"
                                     :srcset="'https://flagcdn.com/w40/' + code + '.png 1x, https://flagcdn.com/w80/' + code + '.png 2x'"
                                     :alt="activeGroup().kicker || activeGroup().title"
                                     width="40" height="27">
                            </span>
                        </template>
                        <div class="min-w-0">
                            <p class="branch-kicker" x-text="activeGroup().kicker"></p>
                            <h2 class="branch-title" x-text="activeGroup().title"></h2>
                        </div>
                    </div>
                    <template x-for="(zone, zi) in visibleZones(activeGroup())" :key="activeGroup().key + '-z-' + zi">
                        <div class="mt-5">
                            <h3 class="branch-zone" x-show="zone.title" x-text="zone.title"></h3>
                            <ul class="branch-grid" :class="zone.title ? '' : 'mt-0'">
                                <template x-for="(name, i) in visibleNames(zone.branches)" :key="activeGroup().key + '-' + zi + '-' + i + '-' + name">
                                    <li class="branch-chip" x-text="name"></li>
                                </template>
                            </ul>
                        </div>
                    </template>
                    <p x-show="visibleZones(activeGroup()).length === 0" class="text-slate-500 text-sm mt-4 mb-0">{{ __('cwa.branches.empty') }}</p>
                </section>
            </template>
        </div>
    </div>

    <div class="mt-10 sm:mt-12 rounded-2xl bg-brand-blue text-white p-5 sm:p-6 md:p-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg sm:text-xl font-extrabold m-0">{{ __('cwa.branches.cta_title') }}</h2>
            <p class="mt-1 mb-0 text-white/80 text-sm">{{ __('cwa.branches.cta_body') }}</p>
        </div>
        <a href="{{ route('beyond.membership') }}"
           class="inline-flex justify-center items-center min-h-[2.85rem] rounded-full bg-brand-gold text-brand-blue font-extrabold px-5 py-2.5 hover:bg-[#c4a030] w-full sm:w-auto">
            {{ __('cwa.nav.join') }}
        </a>
    </div>
</div>
<style>
    .branch-card {
        background: #fff;
        border: 1px solid rgba(231, 224, 212, 0.9);
        border-radius: 1rem;
        padding: 1.15rem 1.1rem 1.25rem;
        box-shadow: 0 8px 24px rgba(26, 31, 46, 0.04);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
    }
    @media (min-width: 640px) {
        .branch-card { padding: 1.5rem 1.55rem 1.6rem; }
    }
    @media (hover: hover) {
        .branch-card:hover {
            transform: translateY(-3px);
            border-color: #D4AF37;
            background: #fffdf6;
            box-shadow: 0 14px 32px rgba(0, 61, 130, 0.12);
        }
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
    .branch-zone {
        margin: 0 0 0.15rem;
        font-size: 1.02rem;
        font-weight: 800;
        line-height: 1.35;
        color: #003D82;
    }
    .diaspora-shell {
        display: grid;
        gap: 1.25rem;
        align-items: start;
    }
    @media (min-width: 800px) {
        .diaspora-shell { grid-template-columns: 15.5rem minmax(0, 1fr); }
    }
    .diaspora-nav {
        display: flex;
        flex-direction: row;
        gap: 0.4rem;
        overflow-x: auto;
        padding-bottom: 0.35rem;
        scroll-snap-type: x mandatory;
        -webkit-overflow-scrolling: touch;
    }
    .diaspora-nav .diaspora-link { scroll-snap-align: start; flex: 0 0 auto; width: auto; }
    @media (min-width: 800px) {
        .diaspora-nav {
            flex-direction: column;
            overflow: visible;
            position: sticky;
            top: 5.5rem;
            scroll-snap-type: none;
        }
        .diaspora-nav .diaspora-link { width: 100%; }
    }
    .diaspora-link {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        width: 100%;
        text-align: left;
        border: 1px solid #ece6db;
        background: #fff;
        color: #003D82;
        border-radius: 0.85rem;
        min-height: 2.75rem;
        padding: 0.65rem 0.75rem;
        font-size: 0.88rem;
        font-weight: 800;
        white-space: nowrap;
        cursor: pointer;
    }
    @media (hover: hover) {
        .diaspora-link:hover { border-color: #D4AF37; background: #fffdf6; }
    }
    .diaspora-link.is-on {
        background: #003D82;
        border-color: #003D82;
        color: #fff;
    }
    .branch-flag.is-sm {
        width: 1.55rem;
        height: 1.05rem;
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
        country: 'north_america',
        provinces: @json($provinces),
        diaspora: @json($diaspora),
        init: function () {
            var self = this;
            this.$watch('q', function () {
                var group = self.activeGroup();
                if (group) self.country = group.key;
            });
        },
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
            if (!this.needle()) return this.diaspora || [];
            return (this.diaspora || []).filter(function (g) {
                if (self.match(g.title) || self.match(g.kicker)) return true;
                return self.visibleZones(g).length > 0;
            });
        },
        activeGroup: function () {
            var list = this.visibleGroups();
            var key = this.country;
            for (var i = 0; i < list.length; i++) {
                if (list[i].key === key) return list[i];
            }
            return list[0] || null;
        }
    };
}
</script>
@endpush
