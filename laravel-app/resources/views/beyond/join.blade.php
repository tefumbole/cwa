@extends('beyond.layout')

@section('title', __('cwa.join.title'))
@section('meta_description', __('cwa.join.subtitle'))

@section('content')
<div class="max-w-xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-extrabold text-brand-blue mb-6">{{ \App\Support\SiteContent::text('join.hero_title', __('cwa.join.title')) }}</h1>
            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 text-red-800 px-3 py-2 text-sm">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('beyond.join.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-semibold text-gray-700">{{ __('cwa.join.name') }} <span class="text-red-500">*</span></label>
                    <input required name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2">
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-700">{{ __('cwa.join.diocese') }} <span class="text-red-500">*</span></label>
                        <input required name="diocese" value="{{ old('diocese') }}" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">{{ __('cwa.join.parish') }} <span class="text-red-500">*</span></label>
                        <input required name="parish" value="{{ old('parish') }}" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2">
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-700">{{ __('cwa.join.phone') }} <span class="text-red-500">*</span></label>
                        <input required name="phone" value="{{ old('phone') }}" type="tel" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">{{ __('cwa.join.email') }}</label>
                        <input name="email" value="{{ old('email') }}" type="email" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">{{ __('cwa.join.age') }}</label>
                    <select name="age_range" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2">
                        <option value="">{{ __('cwa.join.age_skip') }}</option>
                        @foreach ($ageRanges as $range)
                            <option value="{{ $range }}" {{ old('age_range') === $range ? 'selected' : '' }}>{{ $range }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">{{ __('cwa.join.message') }}</label>
                    <textarea name="message" rows="4" class="mt-1 w-full rounded-md border border-gray-200 px-3 py-2">{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="w-full bg-brand-gold hover:bg-yellow-400 text-brand-blue font-bold py-3 rounded-full">
                    {{ __('cwa.join.submit') }}
                </button>
                <p class="text-xs text-center text-slate-500">{{ __('cwa.join.hint') }}</p>
            </form>
</div>
@endsection
