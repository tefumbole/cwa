@extends('beyond.layout')

@section('title', __('cwa.donate.title'))
@section('meta_description', __('cwa.donate.meta'))

@section('content')
@php
    $oldPhone = preg_replace('/\D/', '', (string) old('phone', ''));
    if (strpos($oldPhone, '237') === 0) {
        $oldPhone = substr($oldPhone, 3);
    }
    $oldPhone = substr(ltrim($oldPhone, '0'), 0, 9);
@endphp
<div class="py-10 px-4">
    <div class="max-w-md mx-auto">
        <h1 class="text-2xl font-extrabold text-brand-blue text-center">{{ __('cwa.donate.title') }}</h1>
        <p class="mt-3 text-center text-sm text-gray-600">{{ __('cwa.donate.intro') }}</p>

        @if(session('not_permitted'))
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-3 py-2 text-sm">{{ session('not_permitted') }}</div>
        @endif
        @if($errors->any())
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-3 py-2 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('beyond.donate.store') }}" class="mt-6 space-y-5" x-data="donateForm()">
            @csrf
            <input type="hidden" name="operator" :value="operator">
            <input type="hidden" name="donor_name" :value="donorName">
            <div>
                <label class="text-sm font-semibold text-gray-700">{{ __('cwa.donate.amount') }}</label>
                <div class="mt-2 grid grid-cols-3 gap-2">
                    @foreach($presets as $preset)
                        <button type="button"
                                class="rounded-lg border-2 py-2 font-bold text-sm"
                                :class="!custom && amount === {{ $preset }} ? 'border-brand-gold bg-amber-50 text-brand-blue' : 'border-slate-200'"
                                @click="custom = false; amount = {{ $preset }}">
                            {{ number_format($preset, 0, '.', ' ') }}
                        </button>
                    @endforeach
                    <button type="button"
                            class="rounded-lg border-2 py-2 font-bold text-sm"
                            :class="custom ? 'border-brand-gold bg-amber-50 text-brand-blue' : 'border-slate-200'"
                            @click="custom = true">
                        {{ __('cwa.donate.other') }}
                    </button>
                </div>
                <input x-show="custom" x-cloak type="number" min="100" step="100" x-model.number="amount"
                       class="mt-3 w-full rounded-md border border-gray-200 px-3 py-2" placeholder="{{ __('cwa.donate.amount_ph') }}">
                <input type="hidden" name="amount" :value="amount">
            </div>

            <div>
                <label class="text-sm font-semibold text-gray-700">{{ __('cwa.donate.momo') }}</label>
                <div class="mt-1 flex">
                    <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-200 bg-gray-50 text-gray-600 font-semibold">+237</span>
                    <input required name="phone" x-model="phone" type="tel" inputmode="numeric" maxlength="13" placeholder="6XX XXX XXX"
                           class="w-full rounded-r-md border border-gray-200 px-3 py-2"
                           @input="normalizePhone(); scheduleLookup()"
                           @blur="lookupNow()">
                </div>
                <p class="mt-1 text-xs text-slate-500">{{ __('cwa.donate.phone_hint') }}</p>
                <div class="mt-2 min-h-[1.5rem] text-sm" aria-live="polite">
                    <p x-show="looking" x-cloak class="text-slate-500">{{ __('cwa.donate.checking') }}</p>
                    <div x-show="!looking && (operator || donorName)" x-cloak class="flex flex-wrap items-center gap-2">
                        <span x-show="operator === 'mtn'" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-extrabold bg-[#ffcc00] text-[#1a1a1a]">{{ __('cwa.donate.mtn') }}</span>
                        <span x-show="operator === 'orange'" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-extrabold bg-[#ff6600] text-white">{{ __('cwa.donate.orange') }}</span>
                        <span x-show="donorName" class="font-semibold text-brand-blue" x-text="donorName"></span>
                    </div>
                    <p x-show="!looking && operator && !donorName && phone.length === 9" x-cloak class="text-slate-500">{{ __('cwa.donate.unknown_name') }}</p>
                </div>
                <p x-show="operator === 'mtn'" x-cloak class="mt-2 text-xs text-slate-600">{{ __('cwa.donate.ussd_mtn') }}</p>
                <p x-show="operator === 'orange'" x-cloak class="mt-2 text-xs text-slate-600">{{ __('cwa.donate.ussd_orange') }}</p>
            </div>

            <button type="submit" class="w-full bg-brand-gold hover:bg-yellow-500 text-brand-blue font-bold py-3 rounded-full">
                <span x-show="operator === 'mtn'" x-cloak>{{ __('cwa.donate.pay_mtn') }}</span>
                <span x-show="operator === 'orange'" x-cloak>{{ __('cwa.donate.pay_orange') }}</span>
                <span x-show="operator !== 'mtn' && operator !== 'orange'">{{ __('cwa.donate.pay') }}</span>
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', function () {
    Alpine.data('donateForm', function () {
        return {
            amount: {{ (int) old('amount', 5000) }},
            custom: {{ old('amount') && ! in_array((int) old('amount'), $presets, true) ? 'true' : 'false' }},
            phone: @json($oldPhone),
            operator: '',
            donorName: '',
            looking: false,
            timer: null,
            last: '',
            holderUrl: @json(route('beyond.donate.holder')),
            normalizePhone: function () {
                this.phone = String(this.phone || '').replace(/\D/g, '').replace(/^237/, '').replace(/^0/, '').slice(0, 9);
            },
            resetLookup: function () {
                this.operator = '';
                this.donorName = '';
                this.looking = false;
                this.last = '';
            },
            scheduleLookup: function () {
                var self = this;
                clearTimeout(this.timer);
                this.timer = setTimeout(function () { self.lookupNow(); }, 450);
            },
            lookupNow: function () {
                var digits = String(this.phone || '').replace(/\D/g, '');
                if (digits.length < 9) {
                    this.resetLookup();
                    return;
                }
                if (digits === this.last) return;
                this.last = digits;
                this.looking = true;
                var self = this;
                fetch(this.holderUrl + '?phone=' + encodeURIComponent(digits), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (res) {
                    self.looking = false;
                    if (!res) return;
                    self.operator = res.operator || '';
                    self.donorName = res.name || '';
                })
                .catch(function () { self.looking = false; });
            }
        };
    });
});
</script>
@endpush
