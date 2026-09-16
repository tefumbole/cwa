@php
    $locale = app()->getLocale();
    $variant = $variant ?? 'dark';
    $enOn = $locale === 'en';
    $frOn = $locale === 'fr';
    if ($variant === 'light') {
        $on = 'bg-[#d4af37] text-[#071a38]';
        $off = 'text-[#0a1c3d] border border-[#d1d5db] hover:border-[#d4af37]';
    } else {
        $on = 'bg-brand-gold text-brand-blue';
        $off = 'text-white border border-white/20 hover:text-brand-gold';
    }
@endphp
<div class="flex items-center gap-1 text-xs font-semibold" role="navigation" aria-label="{{ __('cwa.lang.label') }}">
    <a href="{{ url('/lang/en') }}" class="px-2 py-1 rounded {{ $enOn ? $on : $off }}" @if($enOn) aria-current="true" @endif>EN</a>
    <a href="{{ url('/lang/fr') }}" class="px-2 py-1 rounded {{ $frOn ? $on : $off }}" @if($frOn) aria-current="true" @endif>FR</a>
</div>
