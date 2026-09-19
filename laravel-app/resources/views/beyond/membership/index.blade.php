@extends('beyond.layout')

@section('title', __('cwa.membership.page_title'))
@section('meta_description', __('cwa.membership.page_title'))

@section('content')
@php
    $readArticles = !empty($readArticles);
    $readBylaws = !empty($readBylaws);
    $openDocs = $readArticles || $readBylaws || request()->boolean('open');
@endphp
<div class="bg-brand-blue text-white" style="min-height: calc(100vh - 5rem);">
    <div class="max-w-3xl mx-auto px-4 py-16 md:py-24 text-center"
         x-data="membershipGate({{ $openDocs ? 'true' : 'false' }}, {{ $readArticles ? 'true' : 'false' }}, {{ $readBylaws ? 'true' : 'false' }})">
        <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight mb-10">{{ __('cwa.membership.page_title') }}</h1>

        <button type="button" @click="onSubscribe()"
                class="inline-flex items-center justify-center gap-2 w-full sm:w-auto min-h-[3.25rem] px-10 py-3.5 rounded-full bg-brand-gold text-brand-blue font-extrabold text-lg shadow-lg shadow-brand-gold/25 hover:bg-[#b5952f]">
            <i data-lucide="heart" class="w-5 h-5"></i>
            {{ __('cwa.membership.subscribe') }}
        </button>

        <div x-show="docs" x-cloak class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('beyond.membership.bylaws') }}"
               class="inline-flex items-center justify-center gap-2 min-h-[2.75rem] w-full sm:w-auto px-7 py-2.5 rounded-full border-2 border-white text-white font-extrabold hover:bg-white hover:text-brand-blue">
                {{ __('cwa.membership.bylaws_btn') }}
            </a>
            <a href="{{ route('beyond.membership.articles') }}"
               class="inline-flex items-center justify-center gap-2 min-h-[2.75rem] w-full sm:w-auto px-7 py-2.5 rounded-full border-2 border-white text-white font-extrabold hover:bg-white hover:text-brand-blue">
                {{ __('cwa.membership.articles_btn') }}
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function membershipGate(startOpen, readArticles, readBylaws) {
    return {
        docs: !!startOpen,
        readArticles: !!readArticles,
        readBylaws: !!readBylaws,
        registerUrl: @json(route('beyond.membership.register')),
        agreeMsg: @json(__('cwa.membership.agree_unread')),
        onSubscribe: function () {
            if (!this.docs) {
                this.docs = true;
                return;
            }
            if (!this.readArticles || !this.readBylaws) {
                if (!window.confirm(this.agreeMsg)) {
                    return;
                }
            }
            window.location.href = this.registerUrl;
        }
    };
}
</script>
@endpush
