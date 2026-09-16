@extends('beyond.layout')

@section('title', __('cwa.about.title'))
@section('meta_description', __('cwa.about.meta'))

@php
    $aboutHero = \App\Support\SiteContent::image('about.about_image', url('public/branding/cwa-about-home.jpg'));
    $vision = \App\Support\SiteContent::text('about.vision_text', __('cwa.about.vision_text'));
    $mission = \App\Support\SiteContent::text('about.mission_text', __('cwa.about.mission_text'));
    $story = \App\Support\SiteContent::text('about.story_text', __('cwa.about.story_text'));
    $objectives = trans('cwa.about.objectives');
    $missionCards = trans('cwa.about.mission_cards');
    $programs = trans('cwa.about.programs');
    $structure = trans('cwa.about.structure');
    $identity = trans('cwa.about.identity');
    $timeline = trans('cwa.about.timeline');
    $missionIcons = ['church', 'home', 'sparkles', 'heart'];
    $programIcons = ['book-open', 'home', 'sparkles', 'award', 'activity', 'hand', 'users', 'globe'];
@endphp

@section('content')

<section class="relative min-h-[52vh] flex items-center text-white overflow-hidden">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image:url('{{ $aboutHero }}');"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-[#041830] via-[#041830]/78 to-[#041830]/35"></div>
    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <p class="uppercase tracking-[0.28em] text-brand-gold text-xs font-bold mb-4">{{ __('cwa.about.kicker') }}</p>
        <h1 class="text-4xl md:text-6xl font-extrabold leading-tight">
            {{ \App\Support\SiteContent::text('about.hero_title', __('cwa.about.hero_title')) }}
        </h1>
        <p class="mt-5 max-w-2xl text-lg md:text-xl text-blue-100 leading-relaxed">
            {{ \App\Support\SiteContent::text('about.hero_subtitle', __('cwa.about.hero_subtitle')) }}
        </p>
    </div>
</section>

<section class="bg-brand-blue text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
        <div>
            <p class="text-3xl md:text-4xl font-extrabold text-brand-gold">1964</p>
            <p class="mt-1 text-sm text-blue-100">{{ __('cwa.about.stat_founded') }}</p>
        </div>
        <div>
            <p class="text-3xl md:text-4xl font-extrabold text-brand-gold">18,000+</p>
            <p class="mt-1 text-sm text-blue-100">{{ __('cwa.about.stat_members') }}</p>
        </div>
        <div>
            <p class="text-3xl md:text-4xl font-extrabold text-brand-gold">Bamenda</p>
            <p class="mt-1 text-sm text-blue-100">{{ __('cwa.about.stat_seat') }}</p>
        </div>
        <div>
            <p class="text-3xl md:text-4xl font-extrabold text-brand-gold">60</p>
            <p class="mt-1 text-sm text-blue-100">{{ __('cwa.about.stat_years') }}</p>
        </div>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid lg:grid-cols-12 gap-10 items-start">
        <div class="lg:col-span-7">
            <p class="uppercase tracking-[0.2em] text-brand-gold text-xs font-bold">{{ __('cwa.about.story_kicker') }}</p>
            <h2 class="mt-2 text-3xl md:text-4xl font-extrabold text-brand-blue">{{ \App\Support\SiteContent::text('about.story_heading', __('cwa.about.story_heading')) }}</h2>
            <p class="mt-5 text-slate-700 leading-relaxed text-lg">{{ $story }}</p>
            <div class="mt-6 flex flex-wrap gap-2">
                @foreach ($identity as $pill)
                    <span class="px-3 py-1 rounded-full bg-amber-50 text-brand-blue text-sm font-semibold border border-amber-100">{{ $pill }}</span>
                @endforeach
            </div>
        </div>
        <ol class="lg:col-span-5 space-y-4">
            @foreach ($timeline as $item)
                <li class="flex gap-4 bg-slate-50 rounded-2xl border border-slate-100 p-5">
                    <span class="shrink-0 w-16 text-brand-gold font-extrabold">{{ $item['year'] }}</span>
                    <p class="text-slate-700">{{ $item['text'] }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

<section class="py-16 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-2 gap-6">
        <article class="rounded-2xl bg-white border border-slate-100 p-8 shadow-sm">
            <p class="uppercase tracking-[0.2em] text-brand-gold text-xs font-bold">{{ __('cwa.about.motto_label') }}</p>
            <blockquote class="mt-4 text-2xl font-semibold text-brand-blue leading-snug">
                “{{ \App\Support\SiteContent::text('about.motto_text', __('cwa.about.motto_text')) }}”
            </blockquote>
            <p class="mt-3 text-slate-500 font-medium">{{ \App\Support\SiteContent::text('about.motto_ref', __('cwa.about.motto_ref')) }}</p>
        </article>
        <article class="rounded-2xl bg-brand-blue text-white p-8 shadow-sm">
            <p class="uppercase tracking-[0.2em] text-brand-gold text-xs font-bold">{{ __('cwa.about.patron_label') }}</p>
            <h3 class="mt-3 text-2xl font-bold">{{ \App\Support\SiteContent::text('about.patron_title', __('cwa.about.patron_title')) }}</h3>
            <p class="mt-3 text-blue-100">{{ __('cwa.about.patron_text', ['date' => \App\Support\SiteContent::text('about.patron_feast', __('cwa.about.patron_feast'))]) }}</p>
            <a href="{{ url('/calendar') }}?view=month&amp;year={{ date('Y') }}&amp;month=12" class="inline-flex mt-6 text-brand-gold font-semibold hover:underline">{{ __('cwa.about.patron_link') }}</a>
        </article>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid md:grid-cols-2 gap-6">
        <article class="rounded-2xl border border-slate-100 bg-gradient-to-br from-[#041830] to-brand-blue text-white p-8 shadow-xl">
            <p class="uppercase tracking-[0.2em] text-brand-gold text-xs font-bold">{{ __('cwa.home.vision_label') }}</p>
            <h2 class="mt-2 text-2xl font-bold">{{ \App\Support\SiteContent::text('about.vision_heading', __('cwa.about.vision_heading')) }}</h2>
            <p class="mt-4 text-blue-100 leading-relaxed text-lg">{{ $vision }}</p>
        </article>
        <article class="rounded-2xl border border-amber-100 bg-amber-50 p-8 shadow-xl">
            <p class="uppercase tracking-[0.2em] text-brand-gold text-xs font-bold">{{ __('cwa.home.mission_label') }}</p>
            <h2 class="mt-2 text-2xl font-bold text-brand-blue">{{ \App\Support\SiteContent::text('about.mission_heading', __('cwa.about.mission_heading')) }}</h2>
            <p class="mt-4 text-slate-700 leading-relaxed text-lg">{{ $mission }}</p>
        </article>
    </div>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-8 grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($missionCards as $i => $card)
            <article class="rounded-2xl border border-slate-100 p-6 bg-slate-50">
                <i data-lucide="{{ $missionIcons[$i] ?? 'heart' }}" class="w-7 h-7 text-brand-gold"></i>
                <h3 class="mt-3 font-bold text-brand-blue">{{ $card['title'] }}</h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">{{ $card['text'] }}</p>
            </article>
        @endforeach
    </div>
</section>

<section class="py-16 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <p class="uppercase tracking-[0.2em] text-brand-gold text-xs font-bold">{{ __('cwa.about.objectives_kicker') }}</p>
            <h2 class="mt-2 text-3xl md:text-4xl font-extrabold text-brand-blue">{{ \App\Support\SiteContent::text('about.objectives_heading', __('cwa.about.objectives_heading')) }}</h2>
        </div>
        <ol class="grid md:grid-cols-2 gap-5">
            @foreach ($objectives as $i => $item)
                <li class="flex gap-4 bg-white rounded-2xl border border-slate-100 p-6 shadow-sm hover:shadow-md transition-shadow">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-gold text-brand-blue font-extrabold">{{ $i + 1 }}</span>
                    <p class="text-slate-700 leading-relaxed">{{ $item }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <p class="uppercase tracking-[0.2em] text-brand-gold text-xs font-bold">{{ __('cwa.about.programs_kicker') }}</p>
            <h2 class="mt-2 text-3xl md:text-4xl font-extrabold text-brand-blue">{{ \App\Support\SiteContent::text('about.programs_heading', __('cwa.about.programs_heading')) }}</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach ($programs as $i => $program)
                <article class="rounded-2xl border border-slate-100 p-6 hover:border-brand-gold hover:shadow-md transition-all">
                    <i data-lucide="{{ $programIcons[$i] ?? 'heart' }}" class="w-8 h-8 text-brand-gold"></i>
                    <h3 class="mt-3 font-bold text-brand-blue">{{ is_array($program) ? $program['title'] : $program }}</h3>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="py-16 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <p class="uppercase tracking-[0.2em] text-brand-gold text-xs font-bold">{{ __('cwa.about.structure_kicker') }}</p>
            <h2 class="mt-2 text-3xl md:text-4xl font-extrabold text-brand-blue">{{ \App\Support\SiteContent::text('about.structure_heading', __('cwa.about.structure_heading')) }}</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($structure as $i => $node)
                <article class="relative bg-white rounded-2xl border border-slate-100 p-6">
                    <span class="text-brand-gold font-extrabold tracking-widest text-sm">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <h3 class="mt-2 text-xl font-bold text-brand-blue">{{ $node['title'] }}</h3>
                    <p class="mt-2 text-slate-600 text-sm">{{ $node['text'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

@if(isset($leaders) && $leaders->count())
<section id="leadership" class="py-20 bg-brand-blue">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-4xl font-bold text-white mb-4">{{ \App\Support\SiteContent::text('about.leadership_heading', __('cwa.about.leadership_heading')) }}</h2>
            <div class="h-1 w-24 bg-brand-gold mx-auto"></div>
            <p class="mt-4 text-xl text-gray-300">{{ \App\Support\SiteContent::text('about.leadership_subtext', __('cwa.about.leadership_subtext')) }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            @foreach($leaders as $leader)
                <div class="group flex flex-col items-center text-center">
                    <div class="relative mb-6">
                        <div class="absolute inset-0 bg-gradient-to-br from-brand-gold to-[#F7E7CE] rounded-full blur opacity-75 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="relative w-48 h-48 rounded-full p-1 bg-gradient-to-br from-brand-gold to-[#8a701f]">
                            <div class="w-full h-full rounded-full overflow-hidden border-4 border-brand-blue bg-gray-200">
                                @if($leader->photoPublicUrl())
                                    <img src="{{ $leader->photoPublicUrl() }}" alt="{{ $leader->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-200 text-gray-400">
                                        <i data-lucide="user" class="w-16 h-16"></i>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-white">
                        {{ $leader->name }}
                        @if($leader->country)
                            <span class="ml-1" title="{{ $leader->country }}">{{ $leader->countryFlag() ?: '' }}</span>
                        @endif
                    </h3>
                    <p class="mt-1 text-sm font-semibold uppercase tracking-wide text-brand-gold">{{ $leader->title }}</p>
                    @if($leader->description)
                        <p class="mt-3 text-gray-300 text-sm leading-relaxed max-w-sm">{{ $leader->description }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="py-16 bg-gradient-to-r from-brand-blue to-brand-dark text-white text-center">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="text-3xl font-bold mb-4">{{ \App\Support\SiteContent::text('about.cta_heading', __('cwa.about.cta_heading')) }}</h2>
        <p class="text-xl mb-8 opacity-90">{{ \App\Support\SiteContent::text('about.cta_text', __('cwa.about.cta_text')) }}</p>
        <div class="flex flex-wrap justify-center gap-3">
            <a href="{{ route('beyond.join') }}" class="inline-flex items-center gap-2 bg-brand-gold text-brand-blue font-bold text-lg px-8 py-4 rounded-full hover:bg-white transition-all">
                {{ __('cwa.nav.join') }}
            </a>
            <a href="{{ route('beyond.donate') }}" class="inline-flex items-center gap-2 border-2 border-brand-gold text-brand-gold font-bold text-lg px-8 py-4 rounded-full hover:bg-white/10 transition-all">
                <i data-lucide="heart" class="w-5 h-5"></i> {{ __('cwa.nav.donate') }}
            </a>
            <a href="#contact" class="inline-flex items-center gap-2 border-2 border-white/70 text-white font-bold text-lg px-8 py-4 rounded-full hover:bg-white/10 transition-all">
                {{ __('cwa.about.contact_btn') }}
            </a>
        </div>
    </div>
</section>

@include('beyond.partials.contact_section')

@endsection
