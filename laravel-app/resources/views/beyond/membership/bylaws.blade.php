@extends('beyond.layout')

@section('title', __('cwa.membership.bylaws_title'))
@section('meta_description', __('cwa.membership.bylaws_meta'))

@section('content')
<div class="bg-brand-blue text-white" style="min-height: calc(100vh - 5rem);">
    <div class="bg-brand-dark border-b border-brand-gold/30 py-8">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <a href="{{ route('beyond.membership.register') }}"
               class="inline-flex items-center justify-center gap-2 min-h-[3rem] px-8 py-3 rounded-full bg-brand-gold text-brand-blue font-extrabold hover:bg-[#b5952f]">
                <i data-lucide="heart" class="w-4 h-4"></i>
                {{ __('cwa.membership.subscribe') }}
            </a>
            <h1 class="text-2xl md:text-3xl font-bold text-white mt-6 flex items-center justify-center gap-3">
                <i data-lucide="book-open" class="w-7 h-7 text-brand-gold"></i>
                {{ __('cwa.membership.bylaws_title') }}
            </h1>
            <p class="text-gray-300 mt-2 text-sm max-w-xl mx-auto mb-0">{{ __('cwa.membership.bylaws_hint') }}</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-8 w-full pb-16">
        @foreach ($groups as $group)
            @if (!empty($group['label']))
                <p class="text-brand-gold text-xs font-bold uppercase tracking-widest mt-2 mb-3">{{ $group['label'] }}</p>
            @endif
            @foreach ($group['items'] as $item)
                <details class="membership-head bg-white/5 border-2 border-brand-gold/45 rounded-xl mb-4 overflow-hidden">
                    <summary class="cursor-pointer list-none flex items-center gap-4 px-5 py-4">
                        <span class="flex-shrink-0 w-10 h-10 bg-brand-gold/20 rounded-lg flex items-center justify-center text-brand-gold font-bold text-lg border-2 border-brand-gold/60">
                            {{ $item['badge'] }}
                        </span>
                        <span class="flex-1 min-w-0 text-left text-lg font-bold text-brand-gold flex items-center gap-2">
                            <i data-lucide="{{ $item['icon'] }}" class="w-5 h-5 text-gray-300 shrink-0"></i>
                            {{ $item['heading'] }}
                        </span>
                        <i data-lucide="chevron-down" class="membership-chevron w-5 h-5 text-brand-gold shrink-0"></i>
                    </summary>
                    <div class="px-5 pb-5 pt-0 text-gray-300 leading-relaxed text-sm md:text-base membership-article">
                        {!! $item['body_html'] !!}
                    </div>
                </details>
            @endforeach
        @endforeach

        <div class="text-center mt-8">
            <a href="{{ route('beyond.membership.register') }}"
               class="inline-flex items-center justify-center gap-2 min-h-[3rem] px-8 py-3 rounded-full bg-brand-gold text-brand-blue font-extrabold hover:bg-[#b5952f]">
                {{ __('cwa.membership.subscribe') }}
            </a>
        </div>
    </div>
</div>
<style>
    .membership-head > summary { outline: none; }
    .membership-head > summary::-webkit-details-marker { display: none; }
    .membership-head[open] { border-color: rgba(212, 175, 55, 0.8); background: rgba(255,255,255,0.08); }
    .membership-head[open] .membership-chevron { transform: rotate(180deg); }
    .membership-chevron { transition: transform 0.2s ease; }
    .membership-article p { margin: 0 0 0.65rem; }
    .membership-article p:last-child { margin-bottom: 0; }
    .membership-article ul { margin: 0; }
    .membership-article li { color: #d1d5db; }
    .membership-article strong { font-weight: 700; }
</style>
@endsection
