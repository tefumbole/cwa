@extends('beyond.layout')

@section('title', \App\Support\SiteContent::text('events.page_title', __('cwa.event.page_title')))
@section('meta_description', __('cwa.event.subtitle'))

@section('content')
@php
    $filters = [
        'upcoming' => __('cwa.event.upcoming'),
        'featured' => __('cwa.event.featured'),
        'ongoing' => __('cwa.event.ongoing'),
        'past' => __('cwa.event.past'),
    ];
@endphp
<section class="py-12 md:py-16 min-h-[70vh]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-10">
            <p class="text-xs font-extrabold tracking-[0.2em] uppercase text-brand-gold mb-3">CWA Cameroon</p>
            <h1 class="text-3xl md:text-5xl font-extrabold text-brand-blue tracking-tight">{{ \App\Support\SiteContent::text('events.hero_title', __('cwa.event.page_title')) }}</h1>
            <p class="text-slate-500 mt-3 max-w-xl mx-auto text-lg">{{ \App\Support\SiteContent::text('events.hero_subtitle', __('cwa.event.subtitle')) }}</p>
        </div>

        <form method="GET" action="{{ url('/events') }}" class="mb-12 rounded-2xl bg-white border border-stone-200/80 shadow-sm p-4 md:p-5">
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach ($filters as $k => $label)
                    <a href="{{ url('/events') }}?filter={{ $k }}{{ request('q') ? '&q='.urlencode(request('q')) : '' }}{{ request('type') ? '&type='.urlencode(request('type')) : '' }}"
                       class="px-4 py-2 rounded-full text-sm font-semibold transition
                          {{ $filter === $k ? 'bg-brand-blue text-white' : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <div class="flex flex-col md:flex-row gap-3">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <label class="sr-only" for="event-q">{{ __('cwa.event.search') }}</label>
                <input id="event-q" type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('cwa.event.search_ph') }}"
                       class="flex-1 rounded-full border border-stone-200 bg-stone-50 px-5 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-brand-gold/50 focus:border-brand-gold">
                <select name="type" class="rounded-full border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
                    <option value="">{{ __('cwa.event.all_types') }}</option>
                    @foreach(\App\Event::TYPES as $k => $label)
                        <option value="{{ $k }}" {{ request('type') === $k ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-6 py-3 rounded-full bg-brand-gold text-brand-blue font-extrabold text-sm hover:bg-[#c4a030]">
                    {{ __('cwa.event.search') }}
                </button>
            </div>
        </form>

        @if ($events->isEmpty())
            <div class="text-center py-16 rounded-3xl bg-white border border-stone-200/80">
                <div class="mx-auto w-16 h-16 rounded-full bg-brand-blue/10 flex items-center justify-center mb-5">
                    <i data-lucide="calendar" class="w-8 h-8 text-brand-blue"></i>
                </div>
                <h2 class="text-2xl font-extrabold text-brand-blue mb-2">{{ \App\Support\SiteContent::text('events.empty_heading', __('cwa.event.empty_heading')) }}</h2>
                <p class="text-slate-500 max-w-md mx-auto mb-0">{{ \App\Support\SiteContent::text('events.empty_text', __('cwa.event.empty_text')) }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($events as $row)
                    @php
                        $ev = $row['event'];
                        $pub = $row['pub'];
                        $flyer = $row['flyer'];
                        $countdownAt = $row['countdown_at'] ?? null;
                        $status = $row['public_status'];
                        $statusLabels = trans('cwa.event.statuses');
                        $statusColors = [
                            'coming_soon' => 'bg-brand-blue text-white',
                            'setup_in_progress' => 'bg-amber-500 text-white',
                            'happening_today' => 'bg-emerald-600 text-white',
                            'event_in_progress' => 'bg-brand-gold text-brand-blue',
                            'completed' => 'bg-stone-600 text-white',
                            'postponed' => 'bg-orange-500 text-white',
                            'cancelled' => 'bg-red-600 text-white',
                        ];
                    @endphp
                    <article class="bg-white rounded-2xl border border-stone-200/80 hover:shadow-lg hover:-translate-y-0.5 transition-all overflow-hidden flex flex-col">
                        <a href="{{ url('/events/' . $ev->slug) }}" class="block relative">
                            <div class="relative aspect-[4/3] overflow-hidden bg-stone-100">
                                @if ($flyer)
                                    <img src="{{ $flyer }}" alt="{{ $pub->public_title ?: $ev->name }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-blue to-[#0066CC] min-h-[220px]">
                                        <i data-lucide="calendar" class="w-14 h-14 text-brand-gold/80"></i>
                                    </div>
                                @endif
                                @if($status)
                                    <span class="absolute bottom-3 left-3 text-xs font-bold px-3 py-1 rounded-full {{ $statusColors[$status] ?? 'bg-stone-700 text-white' }}">
                                        {{ $statusLabels[$status] ?? $status }}
                                    </span>
                                @endif
                            </div>
                        </a>
                        @if($countdownAt && optional($pub)->show_countdown)
                            <div class="p-3">
                                @include('beyond.partials.event_countdown', [
                                    'targetIso' => $countdownAt->toIso8601String(),
                                    'timezone' => $ev->timezone ?: 'Africa/Douala',
                                    'completionMessage' => $pub->countdown_completion_message ?: __('cwa.event.here'),
                                    'hideAfter' => false,
                                    'compact' => true,
                                ])
                            </div>
                        @endif
                        <div class="px-5 pb-5 pt-3">
                            <a href="{{ url('/events/' . $ev->slug) }}" class="inline-flex items-center gap-2 text-brand-blue font-bold text-sm">
                                {{ __('cwa.event.view_details') }} <i data-lucide="arrow-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
