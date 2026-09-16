@extends('beyond.layout')

@section('title', $title.' — CWA Cameroon')
@section('meta_description', __($metaKey))

@section('content')
<div class="bg-brand-blue text-white flex flex-col" style="min-height: calc(100vh - 5rem);">

    <div class="bg-brand-dark border-b border-brand-gold/30 py-8 sticky top-20 z-30 shadow-lg">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center justify-center gap-3">
                <i data-lucide="shield-check" class="w-8 h-8 text-brand-gold"></i>
                {{ $title }}
            </h1>
            <p class="text-gray-300 mt-2 text-sm">{{ __('cwa.membership.intro') }}</p>
            <p class="text-brand-gold text-xs font-bold uppercase tracking-widest mt-3">{{ __('cwa.membership.chapter', ['current' => $chapterNum, 'total' => 2]) }} · {{ $kicker }}</p>
            @if (!empty($pdfUrl))
                <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 mt-3 text-sm text-brand-gold hover:underline">
                    <i data-lucide="download" class="w-4 h-4"></i> {{ __('cwa.membership.download_pdf') }}
                </a>
            @endif
        </div>
    </div>

    @if (session('warning'))
        <div class="max-w-4xl mx-auto px-4 pt-4 w-full">
            <div class="bg-amber-500/20 border border-amber-400/40 text-amber-100 rounded-lg px-4 py-3 text-sm">{{ session('warning') }}</div>
        </div>
    @endif

    <div class="flex-1 max-w-4xl mx-auto px-4 py-8 w-full">
        <div class="bg-[#002244] rounded-2xl p-6 md:p-10 border-2 border-brand-gold/40 shadow-[0_0_40px_rgba(212,175,55,0.12)] overflow-y-auto max-h-[65vh]">

            <div class="mb-8 p-6 bg-blue-900/30 rounded-lg border-l-4 border-brand-gold">
                <h2 class="text-xl font-bold text-white mb-2">{{ $preambleTitle ?: $title }}</h2>
                <div class="text-gray-300 text-sm md:text-base leading-relaxed space-y-3">
                    @if ($preamble)
                        {!! $preamble !!}
                    @else
                        <p>{{ __('cwa.membership.intro') }}</p>
                    @endif
                    <p class="text-gray-400 text-sm mb-0">
                        {{ $acceptPrompt }}
                    </p>
                </div>
            </div>

            @foreach ($articles as $i => $article)
                <div class="bg-white/5 border-2 border-brand-gold/45 rounded-xl p-6 mb-6 hover:bg-white/10 hover:border-brand-gold/80 transition-colors duration-300">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0 w-10 h-10 bg-brand-gold/20 rounded-lg flex items-center justify-center text-brand-gold font-bold text-lg border-2 border-brand-gold/60">
                            {{ $i + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-lg md:text-xl font-bold text-brand-gold mb-3 flex items-center gap-2">
                                <i data-lucide="{{ $article['icon'] ?? 'info' }}" class="w-5 h-5 text-gray-300 shrink-0"></i>
                                {{ $article['heading'] ?? $article['title'] }}
                            </h3>
                            <div class="text-gray-300 leading-relaxed text-sm md:text-base space-y-3 membership-article">
                                {!! $article['body_html'] ?? $article['body'] !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-brand-dark border-t border-brand-gold/30 py-6 sticky bottom-0 z-[55] shadow-[0_-5px_20px_rgba(0,0,0,0.5)]">
        <div class="max-w-4xl mx-auto px-4 pr-24 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-gray-400 text-center sm:text-left">{{ $acceptPrompt }}</p>
            <div class="flex gap-4 w-full sm:w-auto">
                <button type="button" onclick="window.scrollTo({top:0,behavior:'smooth'})"
                        class="flex-1 sm:flex-none px-6 py-2.5 rounded-md border border-red-500/50 text-red-400 hover:bg-red-950/30 font-medium">
                    {{ __('cwa.membership.disagree') }}
                </button>
                <form method="POST" action="{{ route($agreeRoute) }}" class="flex-1 sm:flex-none">
                    @csrf
                    <button type="submit"
                            class="w-full !bg-brand-gold !text-brand-blue hover:!bg-[#b5952f] font-bold px-8 py-2.5 rounded-md shadow-lg shadow-brand-gold/20 border border-brand-gold/40 flex items-center justify-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4"></i> {{ __('cwa.membership.agree') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<style>
    .membership-article p { margin: 0 0 0.65rem; }
    .membership-article p:last-child { margin-bottom: 0; }
    .membership-article ul { margin: 0; }
    .membership-article li { color: #d1d5db; }
    .membership-article strong { font-weight: 700; }
</style>
@endsection
