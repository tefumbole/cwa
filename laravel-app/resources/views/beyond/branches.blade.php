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
            <section class="branch-card rounded-2xl bg-white border border-stone-200/80 shadow-sm p-6 md:p-7">
                <p class="text-[0.7rem] font-extrabold uppercase tracking-[0.16em] text-brand-gold mb-1" x-text="province.seat"></p>
                <h2 class="text-lg md:text-xl font-extrabold text-brand-blue leading-snug" x-text="province.title"></h2>
                <ul class="mt-4 grid gap-2 sm:grid-cols-2 m-0 p-0 list-none">
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
            <section class="branch-card rounded-2xl bg-white border border-stone-200/80 shadow-sm p-6 md:p-7">
                <h2 class="text-lg md:text-xl font-extrabold text-brand-blue" x-text="group.title"></h2>
                <template x-for="(zone, zi) in visibleZones(group)" :key="group.key + '-z-' + zi">
                    <div class="mt-4">
                        <h3 class="text-sm font-extrabold text-[#002855] mb-2" x-show="zone.title" x-text="zone.title"></h3>
                        <ul class="grid gap-2 sm:grid-cols-2 m-0 p-0 list-none">
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
    .branch-card { transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease; }
    .branch-card:hover {
        transform: translateY(-3px);
        border-color: #D4AF37;
        background: #fffdf6;
        box-shadow: 0 14px 32px rgba(0, 61, 130, 0.12);
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
                if (self.match(g.title)) return true;
                return self.visibleZones(g).length > 0;
            });
        }
    };
}
</script>
@endpush
