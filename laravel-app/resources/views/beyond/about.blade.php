@extends('beyond.layout')

@section('title', __('cwa.about.title'))
@section('meta_description', __('cwa.about.meta'))

@php
    $vision = \App\Support\SiteContent::text('about.vision_text', __('cwa.about.vision_text'));
    $mission = \App\Support\SiteContent::text('about.mission_text', __('cwa.about.mission_text'));
    $story = \App\Support\SiteContent::text('about.story_text', __('cwa.about.story_text'));
    $objectives = trans('cwa.about.objectives');
    $programs = trans('cwa.about.programs');
    $structure = trans('cwa.about.structure');
@endphp

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-extrabold text-brand-blue">{{ \App\Support\SiteContent::text('about.hero_title', __('cwa.about.hero_title')) }}</h1>
    <p class="mt-3 text-slate-700 leading-relaxed">{{ $story }}</p>

    <h2 class="mt-10 text-lg font-bold text-brand-blue">{{ \App\Support\SiteContent::text('about.vision_heading', __('cwa.about.vision_heading')) }}</h2>
    <p class="mt-2 text-slate-700 leading-relaxed">{{ $vision }}</p>

    <h2 class="mt-8 text-lg font-bold text-brand-blue">{{ \App\Support\SiteContent::text('about.mission_heading', __('cwa.about.mission_heading')) }}</h2>
    <p class="mt-2 text-slate-700 leading-relaxed">{{ $mission }}</p>

    <h2 class="mt-8 text-lg font-bold text-brand-blue">{{ \App\Support\SiteContent::text('about.motto_label', __('cwa.about.motto_label')) }}</h2>
    <p class="mt-2 text-slate-700">“{{ \App\Support\SiteContent::text('about.motto_text', __('cwa.about.motto_text')) }}” — {{ \App\Support\SiteContent::text('about.motto_ref', __('cwa.about.motto_ref')) }}</p>

    <h2 class="mt-8 text-lg font-bold text-brand-blue">{{ \App\Support\SiteContent::text('about.patron_title', __('cwa.about.patron_title')) }}</h2>
    <p class="mt-2 text-slate-700">{{ __('cwa.about.patron_text', ['date' => \App\Support\SiteContent::text('about.patron_feast', __('cwa.about.patron_feast'))]) }}</p>

    <h2 class="mt-8 text-lg font-bold text-brand-blue">{{ \App\Support\SiteContent::text('about.objectives_heading', __('cwa.about.objectives_heading')) }}</h2>
    <ol class="mt-3 list-decimal pl-5 space-y-2 text-slate-700">
        @foreach ($objectives as $item)
            <li>{{ $item }}</li>
        @endforeach
    </ol>

    <h2 class="mt-8 text-lg font-bold text-brand-blue">{{ \App\Support\SiteContent::text('about.programs_heading', __('cwa.about.programs_heading')) }}</h2>
    <ul class="mt-3 list-disc pl-5 space-y-1 text-slate-700">
        @foreach ($programs as $program)
            <li>{{ is_array($program) ? $program['title'] : $program }}</li>
        @endforeach
    </ul>

    <h2 class="mt-8 text-lg font-bold text-brand-blue">{{ \App\Support\SiteContent::text('about.structure_heading', __('cwa.about.structure_heading')) }}</h2>
    <ul class="mt-3 space-y-2 text-slate-700">
        @foreach ($structure as $node)
            <li><strong>{{ $node['title'] }}.</strong> {{ $node['text'] }}</li>
        @endforeach
    </ul>

    @if(isset($leaders) && $leaders->count())
        <h2 class="mt-8 text-lg font-bold text-brand-blue">{{ \App\Support\SiteContent::text('about.leadership_heading', __('cwa.about.leadership_heading')) }}</h2>
        <ul class="mt-3 space-y-3">
            @foreach($leaders as $leader)
                <li>
                    <strong>{{ $leader->name }}</strong>
                    @if($leader->title)<span class="text-slate-600"> — {{ $leader->title }}</span>@endif
                </li>
            @endforeach
        </ul>
    @endif
</div>

@include('beyond.partials.contact_section')
@endsection
