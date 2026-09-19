@extends('beyond.layout')

@section('title', $title)
@section('meta_description', $meta)

@section('content')
<div class="bg-brand-blue text-white" style="min-height: calc(100vh - 5rem);">
    <div class="bg-brand-dark border-b border-brand-gold/30 py-8">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center justify-center gap-3">
                <i data-lucide="{{ $icon ?? 'book-open' }}" class="w-7 h-7 text-brand-gold"></i>
                {{ $title }}
            </h1>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-8 w-full pb-16">
        @foreach ($items as $item)
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

        <div class="text-center mt-10 mb-4">
            <a href="{{ route('beyond.membership', ['open' => 1]) }}"
               class="inline-flex items-center justify-center gap-2 min-h-[3rem] px-10 py-3 rounded-full bg-white text-brand-blue font-extrabold hover:bg-brand-gold">
                {{ __('cwa.membership.close') }}
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
    .membership-article { padding-top: 0.35rem; }
    .membership-article .cwa-block + .cwa-block { margin-top: 1.15rem; padding-top: 1rem; border-top: 1px solid rgba(212,175,55,0.22); }
    .membership-article .cwa-section {
        margin: 0 0 0.7rem;
        color: #e8c96a;
        font-weight: 800;
        letter-spacing: 0.04em;
        font-size: 0.95rem;
    }
    .membership-article .cwa-para,
    .membership-article .cwa-lead { margin: 0 0 0.55rem; color: #e5e7eb; }
    .membership-article .cwa-points {
        margin: 0;
        padding-left: 1.35rem;
        list-style: decimal;
    }
    .membership-article .cwa-points > li {
        color: #e5e7eb;
        margin: 0 0 0.7rem;
        padding-left: 0.35rem;
        line-height: 1.55;
    }
    .membership-article .cwa-points > li::marker {
        color: #d4af37;
        font-weight: 800;
    }
    .membership-article .cwa-letters,
    .membership-article .cwa-dashes {
        margin: 0.45rem 0 0;
        padding-left: 1.15rem;
    }
    .membership-article .cwa-letters { list-style: lower-alpha; }
    .membership-article .cwa-dashes { list-style: disc; }
    .membership-article .cwa-letters > li,
    .membership-article .cwa-dashes > li {
        color: #d1d5db;
        margin: 0 0 0.35rem;
        line-height: 1.5;
    }
    .membership-article .cwa-letters > li::marker,
    .membership-article .cwa-dashes > li::marker { color: #d4af37; }
    .membership-article strong { font-weight: 700; color: #fff; }
    .membership-article .cwa-motto { color: #e8c96a; }
</style>
@endsection
