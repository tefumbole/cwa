@extends('beyond.layout')

@section('title', __('cwa.calendar.title'))
@section('meta_description', __('cwa.calendar.subtitle'))

@php
    $monthNames = trans('cwa.calendar.months');
    $monthList = $events->all();
    if ($view === 'month' && $observance && (int) $month === 12) {
        $monthList[] = $observance;
        usort($monthList, function ($a, $b) {
            $da = isset($a['event']) ? optional($a['event']->event_start_at)->timestamp : optional($a['starts_at'])->timestamp;
            $db = isset($b['event']) ? optional($b['event']->event_start_at)->timestamp : optional($b['starts_at'])->timestamp;
            return ($da ?: 0) - ($db ?: 0);
        });
    }
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-extrabold text-brand-blue mb-6">{{ \App\Support\SiteContent::text('events.hero_title', __('cwa.calendar.heading')) }}</h1>

        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div class="inline-flex rounded-full bg-white border border-slate-200 p-1">
                <a href="{{ url('/calendar') }}?view=month&amp;year={{ $year }}&amp;month={{ $month }}"
                   class="px-4 py-1.5 rounded-full text-sm font-semibold {{ $view === 'month' ? 'bg-brand-blue text-white' : 'text-slate-600' }}">{{ __('cwa.calendar.month') }}</a>
                <a href="{{ url('/calendar') }}?view=year&amp;year={{ $year }}"
                   class="px-4 py-1.5 rounded-full text-sm font-semibold {{ $view === 'year' ? 'bg-brand-blue text-white' : 'text-slate-600' }}">{{ __('cwa.calendar.yearly') }}</a>
            </div>
            <form method="GET" action="{{ url('/calendar') }}" class="flex flex-wrap items-center gap-2">
                <input type="hidden" name="view" value="{{ $view }}">
                @if ($view === 'month')
                    <select name="month" class="rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white">
                        @foreach ($monthNames as $num => $label)
                            <option value="{{ $num }}" {{ (int) $month === $num ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                @endif
                <select name="year" class="rounded-lg border border-gray-300 px-3 py-2 text-sm bg-white">
                    @foreach ($years as $y)
                        <option value="{{ $y }}" {{ (int) $year === (int) $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-brand-blue text-white text-sm font-semibold rounded-lg">{{ __('cwa.calendar.show') }}</button>
            </form>
        </div>

        <div class="flex flex-wrap gap-1.5 mb-6">
            @foreach ($monthNames as $num => $label)
                <a href="{{ url('/calendar') }}?view=month&amp;year={{ $year }}&amp;month={{ $num }}"
                   class="px-3 py-1 rounded-full text-xs font-semibold {{ $view === 'month' && (int) $month === (int) $num ? 'bg-brand-gold text-brand-blue' : 'bg-white border border-slate-200 text-slate-600 hover:border-brand-gold' }}">{{ $label }}</a>
            @endforeach
        </div>

        @if ($view === 'month')
            <div class="flex items-center justify-between mb-6">
                <a href="{{ url('/calendar') }}?view=month&amp;year={{ $prev->year }}&amp;month={{ $prev->month }}"
                   class="text-brand-blue font-semibold text-sm">&larr; {{ $monthNames[$prev->month] ?? $prev->format('F') }}</a>
                <h2 class="text-2xl font-extrabold text-brand-blue">{{ $monthLabel }}</h2>
                <a href="{{ url('/calendar') }}?view=month&amp;year={{ $next->year }}&amp;month={{ $next->month }}"
                   class="text-brand-blue font-semibold text-sm">{{ $monthNames[$next->month] ?? $next->format('F') }} &rarr;</a>
            </div>

            @if (empty($monthList))
                <div class="text-center py-16 bg-white rounded-2xl border border-slate-100">
                    <i data-lucide="calendar" class="w-14 h-14 text-gray-300 mx-auto mb-3"></i>
                    <h3 class="text-xl font-bold text-gray-800">{{ \App\Support\SiteContent::text('events.empty_heading', __('cwa.calendar.empty_heading')) }}</h3>
                    <p class="mt-2 text-gray-600">{{ \App\Support\SiteContent::text('events.empty_text', __('cwa.calendar.empty_text')) }}</p>
                </div>
            @else
                <ol class="space-y-3">
                    @foreach ($monthList as $row)
                        @include('beyond.partials.calendar_row', ['row' => $row])
                    @endforeach
                </ol>
            @endif
        @else
            <h2 class="text-2xl font-extrabold text-brand-blue mb-6">{{ $year }}</h2>
            @php
                $any = false;
                foreach (range(1, 12) as $m) {
                    if (!empty($grouped[$m])) { $any = true; break; }
                }
                $undated = $grouped[0] ?? [];
            @endphp
            @if (! $any && empty($undated))
                <div class="text-center py-16 bg-white rounded-2xl border border-slate-100">
                    <i data-lucide="calendar" class="w-14 h-14 text-gray-300 mx-auto mb-3"></i>
                    <h3 class="text-xl font-bold text-gray-800">{{ \App\Support\SiteContent::text('events.empty_heading', __('cwa.calendar.empty_heading')) }}</h3>
                    <p class="mt-2 text-gray-600">{{ \App\Support\SiteContent::text('events.empty_text', __('cwa.calendar.empty_text')) }}</p>
                </div>
            @else
                @foreach (range(1, 12) as $m)
                    @if (!empty($grouped[$m]))
                        <h3 class="mt-8 mb-3 text-lg font-bold text-brand-blue">
                            <a href="{{ url('/calendar') }}?view=month&amp;year={{ $year }}&amp;month={{ $m }}" class="hover:text-brand-gold">{{ $monthNames[$m] }}</a>
                        </h3>
                        <ol class="space-y-3">
                            @foreach ($grouped[$m] as $row)
                                @include('beyond.partials.calendar_row', ['row' => $row])
                            @endforeach
                        </ol>
                    @endif
                @endforeach
                @if (!empty($undated))
                    <h3 class="mt-8 mb-3 text-lg font-bold text-brand-blue">{{ __('cwa.calendar.undated') }}</h3>
                    <ol class="space-y-3">
                        @foreach ($undated as $row)
                            @include('beyond.partials.calendar_row', ['row' => $row])
                        @endforeach
                    </ol>
                @endif
            @endif
        @endif
    </div>
</div>
@endsection
