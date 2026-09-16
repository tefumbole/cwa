@extends('beyond.layout')

@section('title', __('cwa.donate.title'))
@section('meta_description', __('cwa.donate.meta'))

@section('content')
<div class="min-h-[70vh] bg-slate-50 py-10 px-4">
    <div class="max-w-md mx-auto bg-white rounded-2xl shadow-lg border border-slate-100 p-6">
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

        <form method="POST" action="{{ route('beyond.donate.store') }}" class="mt-6 space-y-5" x-data="{ amount: {{ (int) old('amount', 5000) }}, custom: false }">
            @csrf
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
                    <input required name="phone" value="{{ old('phone') }}" type="tel" maxlength="13" placeholder="6XX XXX XXX"
                           class="w-full rounded-r-md border border-gray-200 px-3 py-2">
                </div>
            </div>

            <button type="submit" class="w-full bg-brand-gold hover:bg-yellow-500 text-brand-blue font-bold py-3 rounded-full">
                {{ __('cwa.donate.pay') }}
            </button>
        </form>
    </div>
</div>
@endsection
