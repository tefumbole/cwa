@extends('beyond.layout')

@php
    $locale = app()->getLocale();
    $isFr = strpos((string) $locale, 'fr') === 0;
    $landingBanner = $isFr
        ? url('public/branding/cwa-landing-fr.png')
        : url('public/branding/cwa-landing-en.png');
@endphp

@section('title', $isFr ? "Association des Femmes Catholiques du Cameroun" : "Catholic Women's Association of Cameroon")
@section('meta_description', $isFr
    ? "Association des Femmes Catholiques du Cameroun — Pour servir et non pour être servi (Matthieu 20:28). 60 ans de foi, de service et d'autonomisation."
    : "Catholic Women's Association of Cameroon — To Serve and Not to Be Served (Matthew 20:28). Six decades of faith, service and empowerment.")

@section('content')

{{-- 60th Anniversary landing banner (EN / FR) --}}
<section class="relative w-full bg-brand-soft">
    <div class="relative w-full">
        <img
            src="{{ $landingBanner }}"
            alt="{{ $isFr ? 'CWA Cameroun — 60e anniversaire' : 'CWA Cameroon — 60th Anniversary' }}"
            class="w-full h-auto block object-cover object-center max-h-[92vh]"
        >
        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-brand-blue/90 via-brand-blue/40 to-transparent pt-24 pb-8 px-4">
            <div class="max-w-5xl mx-auto flex flex-col sm:flex-row items-center justify-center gap-4 flex-wrap">
                <a href="{{ url('/about') }}"
                   class="bg-brand-gold hover:bg-brand-gold-light text-white h-12 px-8 text-base font-bold rounded-full inline-flex items-center justify-center shadow-lg">
                    {{ $isFr ? 'À propos de la CWA' : 'About CWA' }}
                    <i data-lucide="arrow-right" class="ml-2 w-4 h-4"></i>
                </a>
                <a href="{{ url('/about') }}#contact"
                   class="h-12 px-8 text-base font-bold rounded-full border-2 border-white/80 text-white hover:bg-white/15 inline-flex items-center justify-center gap-2">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                    {{ $isFr ? 'Nous contacter' : 'Contact Us' }}
                </a>
                <a href="https://wa.me/237683155315" target="_blank" rel="noopener"
                   class="h-12 px-8 text-base font-bold rounded-full bg-[#25D366] hover:bg-[#1EBE57] text-white inline-flex items-center justify-center gap-2">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>

{{-- Motto strip --}}
<section class="bg-brand-blue text-white py-10">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <p class="text-xl md:text-2xl font-semibold tracking-wide">
            {{ $isFr ? '« Pour servir et non pour être servi »' : '“To Serve and Not to Be Served”' }}
        </p>
        <p class="text-brand-gold mt-2 text-sm md:text-base tracking-widest uppercase">
            {{ $isFr ? '— Matthieu 20:28 —' : '— Matthew 20:28 —' }}
        </p>
        <p class="mt-6 text-white/85 max-w-2xl mx-auto">
            {{ $isFr
                ? 'Six décennies de foi, de service et d\'autonomisation (1964 – 2024).'
                : 'Six decades of faith, service and empowerment (1964 – 2024).' }}
        </p>
    </div>
</section>

{{-- Pillars --}}
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-brand-blue mb-3">
                {{ $isFr ? 'Nos piliers' : 'Our Pillars' }}
            </h2>
            <div class="h-1 w-20 bg-brand-gold mx-auto"></div>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
            @foreach (($isFr
                ? [
                    ['icon' => 'hand-heart', 'label' => 'Service'],
                    ['icon' => 'book-open', 'label' => 'Foi'],
                    ['icon' => 'users', 'label' => 'Unité'],
                    ['icon' => 'heart', 'label' => 'Amour'],
                    ['icon' => 'sparkles', 'label' => 'Autonomisation'],
                ]
                : [
                    ['icon' => 'hand-heart', 'label' => 'Service'],
                    ['icon' => 'book-open', 'label' => 'Faith'],
                    ['icon' => 'users', 'label' => 'Unity'],
                    ['icon' => 'heart', 'label' => 'Love'],
                    ['icon' => 'sparkles', 'label' => 'Empowerment'],
                ]) as $pillar)
                <div class="bg-brand-soft border border-brand-border rounded-xl p-6 text-center hover:shadow-md transition">
                    <div class="text-brand-gold mb-3 flex justify-center"><i data-lucide="{{ $pillar['icon'] }}" class="w-10 h-10"></i></div>
                    <h3 class="text-sm md:text-base font-bold text-brand-blue uppercase tracking-wide">{{ $pillar['label'] }}</h3>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Events teaser --}}
@if(!empty($homeEvents) && $homeEvents->isNotEmpty())
<section class="py-16 bg-brand-soft border-t border-brand-border">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-brand-blue mb-3">
                {{ $isFr ? 'Événements à venir' : 'Upcoming Events' }}
            </h2>
            <div class="h-1 w-20 bg-brand-gold mx-auto"></div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($homeEvents as $ev)
                <a href="{{ url('/events/' . $ev['slug']) }}" class="group block bg-white rounded-xl border border-brand-border hover:border-brand-gold hover:shadow-xl transition overflow-hidden">
                    <div class="relative h-44 bg-brand-light/30 overflow-hidden">
                        @if(!empty($ev['flyer']))
                            <img src="{{ $ev['flyer'] }}" alt="{{ $ev['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-blue to-brand-secondary">
                                <i data-lucide="calendar" class="w-12 h-12 text-white opacity-70"></i>
                            </div>
                        @endif
                        @if(!empty($ev['start']))
                            <span class="absolute top-3 right-3 bg-brand-gold text-white text-xs font-bold px-3 py-1 rounded-full">{{ $ev['start']->format('M d') }}</span>
                        @endif
                    </div>
                    <div class="p-5">
                        <h3 class="text-lg font-bold text-brand-blue group-hover:text-brand-secondary line-clamp-2 mb-2">{{ $ev['title'] }}</h3>
                        @if(!empty($ev['start']))
                            <p class="text-sm text-brand-muted mb-1"><i data-lucide="calendar" class="w-4 h-4 inline text-brand-gold"></i> {{ $ev['start']->format('D, M j, Y g:i A') }}</p>
                        @endif
                        @if(!empty($ev['venue']))
                            <p class="text-sm text-brand-muted line-clamp-1"><i data-lucide="map-pin" class="w-4 h-4 inline text-brand-gold"></i> {{ $ev['venue'] }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
        <div class="text-center mt-10">
            <a href="{{ url('/events') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-brand-primary text-white font-semibold hover:bg-brand-secondary transition">
                {{ $isFr ? 'Voir tous les événements' : 'View all events' }}
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</section>
@endif

{{-- CTA --}}
<section class="py-16 bg-gradient-to-r from-brand-blue via-brand-secondary to-brand-blue">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
            {{ $isFr ? 'Marchez avec nous dans la foi et le service' : 'Walk with us in faith and service' }}
        </h2>
        <p class="text-lg text-white/90 mb-8">
            {{ $isFr
                ? 'Contactez l\'Association des Femmes Catholiques — nous sommes là pour servir.'
                : 'Contact the Catholic Women\'s Association — we are here to serve.' }}
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="https://wa.me/237683155315" target="_blank" rel="noopener"
               class="px-8 py-4 text-lg rounded-lg shadow-xl bg-brand-gold hover:bg-brand-gold-light text-white font-semibold inline-flex items-center justify-center gap-2">
                <i data-lucide="message-circle" class="w-5 h-5"></i> WhatsApp
            </a>
            <a href="mailto:info@cwacam.org"
               class="bg-white text-brand-blue hover:bg-brand-soft px-8 py-4 text-lg rounded-lg shadow-xl font-semibold inline-flex items-center justify-center gap-2">
                <i data-lucide="mail" class="w-5 h-5"></i> {{ $isFr ? 'Écrire' : 'Email Us' }}
            </a>
        </div>
    </div>
</section>

@endsection
