@extends('beyond.layout')

@section('title', __('cwa.resources.title'))
@section('meta_description', __('cwa.resources.subtitle'))

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-extrabold text-brand-blue mb-6">{{ \App\Support\SiteContent::text('resources.hero_title', __('cwa.resources.title')) }}</h1>
    <ul class="space-y-3">
        @foreach ($files as $file)
            <li class="flex items-center justify-between gap-3 border-b border-slate-100 py-3">
                <span class="font-medium text-brand-blue">{{ $file['title'] }} <span class="text-xs text-slate-500">{{ $file['lang'] }}</span></span>
                @if ($file['exists'])
                    <a href="{{ $file['url'] }}" target="_blank" rel="noopener" class="text-sm font-semibold text-brand-blue hover:underline">{{ __('cwa.resources.download') }}</a>
                @else
                    <span class="text-sm text-slate-400">{{ __('cwa.resources.soon') }}</span>
                @endif
            </li>
        @endforeach
    </ul>
</div>
@endsection
