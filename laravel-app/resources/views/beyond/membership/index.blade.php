@extends('beyond.layout')

@section('title', __('cwa.membership.page_title'))
@section('meta_description', __('cwa.membership.page_title'))

@section('content')
@php
    $readArticles = !empty($readArticles);
    $readBylaws = !empty($readBylaws);
    $openDocs = $readArticles || $readBylaws || request()->boolean('open');
@endphp
<div class="min-h-[70vh] px-4 py-12 sm:py-16 md:py-24" x-data="membershipGate({{ $openDocs ? 'true' : 'false' }}, {{ $readArticles ? 'true' : 'false' }}, {{ $readBylaws ? 'true' : 'false' }})">
    <div class="max-w-xl mx-auto text-center">
        <h1 class="text-[2rem] leading-tight sm:text-4xl md:text-5xl font-extrabold text-brand-blue tracking-tight">{{ __('cwa.membership.page_title') }}</h1>
        <button type="button" @click="onSubscribe()"
                class="mt-10 inline-flex items-center justify-center gap-2 w-full sm:w-auto min-h-[3.25rem] px-10 py-3.5 rounded-full bg-brand-gold text-brand-blue font-extrabold text-lg hover:bg-[#c4a030] shadow-sm">
            <i data-lucide="heart" class="w-5 h-5"></i>
            {{ __('cwa.membership.subscribe') }}
        </button>

        <div x-show="groups" x-cloak class="mt-8 text-left">
            <h2 class="text-xl font-extrabold text-brand-blue text-center m-0">{{ __('cwa.membership.where_title') }}</h2>
            <p class="mt-2 mb-5 text-slate-600 text-center">{{ __('cwa.membership.where_lead') }}</p>
            <div class="grid gap-3">
                <button type="button" @click="goGroup('cameroon')"
                        class="w-full text-left rounded-2xl border-2 border-[#003D82] bg-white px-5 py-4 hover:bg-[#003D82] hover:text-white transition-colors">
                    <span class="block font-extrabold text-lg">{{ __('cwa.membership.where_cameroon') }}</span>
                    <span class="block text-sm opacity-80 mt-1">{{ __('cwa.membership.where_cameroon_hint') }}</span>
                </button>
                <button type="button" @click="goGroup('diaspora')"
                        class="w-full text-left rounded-2xl border-2 border-[#D4AF37] bg-white px-5 py-4 hover:bg-[#D4AF37] transition-colors">
                    <span class="block font-extrabold text-lg text-brand-blue">{{ __('cwa.membership.where_diaspora') }}</span>
                    <span class="block text-sm text-slate-600 mt-1">{{ __('cwa.membership.where_diaspora_hint') }}</span>
                </button>
            </div>
        </div>

        <div x-show="docs" x-cloak class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('beyond.membership.bylaws') }}"
               class="inline-flex items-center justify-center gap-2 min-h-[2.75rem] w-full sm:w-auto px-7 py-2.5 rounded-full border-2 border-brand-blue text-brand-blue font-extrabold hover:bg-brand-blue hover:text-white">
                {{ __('cwa.membership.bylaws_btn') }}
            </a>
            <a href="{{ route('beyond.membership.articles') }}"
               class="inline-flex items-center justify-center gap-2 min-h-[2.75rem] w-full sm:w-auto px-7 py-2.5 rounded-full border-2 border-brand-gold text-brand-blue font-extrabold hover:bg-brand-gold">
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
        groups: !!startOpen,
        readArticles: !!readArticles,
        readBylaws: !!readBylaws,
        registerUrl: @json(route('beyond.membership.register')),
        agreeMsg: @json(__('cwa.membership.agree_unread')),
        onSubscribe: function () {
            this.groups = true;
        },
        goGroup: function (kind) {
            if (!this.readArticles || !this.readBylaws) {
                if (!window.confirm(this.agreeMsg)) {
                    return;
                }
            }
            window.location.href = this.registerUrl + '?group=' + encodeURIComponent(kind);
        }
    };
}
</script>
@endpush
