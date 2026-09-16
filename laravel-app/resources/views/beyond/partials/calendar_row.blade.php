@php
    $isObservance = !empty($row['is_observance']);
    if ($isObservance) {
        $start = $row['starts_at'] ?? null;
        $title = $row['title'];
        $venue = $row['venue'] ?? '';
        $type = $row['type_label'] ?? 'Feast day';
        $url = $row['url'] ?? url('/about');
        $time = $start ? $start->locale(app()->getLocale())->isoFormat('D MMM') : __('cwa.calendar.tba');
    } else {
        $ev = $row['event'];
        $start = $ev->event_start_at;
        $title = $row['title'];
        $venue = $row['venue'] ?: ($row['location'] ?? '');
        $type = $row['type_label'] ?? '';
        $url = url('/events/' . $ev->slug);
        $time = $start ? $start->locale(app()->getLocale())->isoFormat('D MMM · HH:mm') : __('cwa.calendar.tba');
    }
@endphp
<li>
    <a href="{{ $url }}" class="flex gap-4 bg-white rounded-2xl border border-slate-100 p-4 sm:p-5 hover:border-brand-gold hover:shadow-md transition-all">
        <div class="shrink-0 w-16 text-center">
            <p class="text-xs font-bold uppercase tracking-wide text-brand-gold">{{ $start ? $start->locale(app()->getLocale())->isoFormat('MMM') : __('cwa.calendar.tba') }}</p>
            <p class="text-2xl font-extrabold text-brand-blue leading-none">{{ $start ? $start->format('d') : '—' }}</p>
        </div>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
                <h3 class="font-bold text-brand-blue">{{ $title }}</h3>
                @if ($type)
                    <span class="text-[11px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full {{ $isObservance ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">{{ $type }}</span>
                @endif
            </div>
            <p class="mt-1 text-sm text-slate-500">{{ $time }}@if($venue) · {{ $venue }}@endif</p>
            @if (!empty($row['summary']))
                <p class="mt-2 text-sm text-slate-600 line-clamp-2">{{ $row['summary'] }}</p>
            @endif
        </div>
    </a>
</li>
