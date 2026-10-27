@extends('beyond.layout')

@section('title', __('cwa.speech.title'))
@section('meta_description', __('cwa.speech.meta'))

@section('content')
<div class="speech-page">
    <div class="speech-wrap">
        <p class="speech-kicker">{{ __('cwa.speech.kicker') }}</p>
        <div class="speech-head">
            <h1>{{ __('cwa.speech.title') }}</h1>
            <div class="speech-lang">
                <span>{{ __('cwa.speech.translate') }}</span>
                @include('beyond.partials.lang_switch', ['variant' => 'light'])
            </div>
        </div>

        <div class="speech-cards">
            @foreach (__('cwa.speech.sections') as $index => $section)
                <article class="speech-card">
                    <span class="speech-num">{{ $index + 1 }}</span>
                    <div class="speech-card-body">
                        <h2>
                            <i data-lucide="{{ $section['icon'] }}"></i>
                            {{ $section['title'] }}
                        </h2>
                        <p>{{ $section['body'] }}</p>
                    </div>
                </article>
            @endforeach

            <article class="speech-card speech-sign">
                <span class="speech-num"><i data-lucide="pen-line"></i></span>
                <div class="speech-card-body">
                    <h2>{{ __('cwa.speech.sign_name') }}</h2>
                    <p class="speech-role">{{ __('cwa.speech.sign_role') }}</p>
                    <p class="speech-org">{{ __('cwa.speech.sign_org') }}</p>
                </div>
            </article>
        </div>
    </div>
</div>
@endsection

@push('head')
<style>
    .speech-page {
        background:
            radial-gradient(900px 420px at 10% -10%, rgba(212, 175, 55, 0.18), transparent 60%),
            linear-gradient(180deg, #f6f1e4 0%, #eef3f8 100%);
        min-height: 70vh;
    }
    .speech-wrap {
        max-width: 52rem;
        margin: 0 auto;
        padding: 2.4rem 1.1rem 4rem;
    }
    .speech-kicker {
        margin: 0 0 0.45rem;
        color: #8a6d1d;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        font-size: 0.72rem;
        font-weight: 800;
    }
    .speech-head {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.4rem;
    }
    .speech-head h1 {
        margin: 0;
        color: #003D82;
        font-family: Fraunces, Georgia, serif;
        font-size: clamp(1.8rem, 4vw, 2.6rem);
        line-height: 1.1;
        font-weight: 700;
    }
    .speech-lang {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        padding: 0.4rem 0.55rem 0.4rem 0.8rem;
        border-radius: 999px;
        background: #fff;
        border: 1px solid rgba(0, 61, 130, 0.12);
        box-shadow: 0 8px 20px rgba(0, 40, 85, 0.06);
    }
    .speech-lang > span {
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #003D82;
    }
    .speech-cards { display: grid; gap: 0.9rem; }
    .speech-card {
        display: flex;
        gap: 0.9rem;
        align-items: flex-start;
        padding: 1.05rem 1.1rem 1.15rem;
        border-radius: 1.15rem;
        background: linear-gradient(180deg, #0b3f86 0%, #062a5c 100%);
        border: 1px solid rgba(212, 175, 55, 0.35);
        box-shadow: 0 14px 30px rgba(0, 40, 85, 0.16);
    }
    .speech-num {
        flex: 0 0 auto;
        width: 2.35rem;
        height: 2.35rem;
        border-radius: 0.7rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #D4AF37;
        color: #3b2a08;
        font-weight: 800;
        font-size: 1rem;
    }
    .speech-num svg { width: 1.05rem; height: 1.05rem; }
    .speech-card-body { min-width: 0; }
    .speech-card h2 {
        display: flex;
        align-items: center;
        gap: 0.45rem;
        margin: 0.15rem 0 0.45rem;
        color: #D4AF37;
        font-size: 1.05rem;
        font-weight: 800;
        line-height: 1.3;
    }
    .speech-card h2 svg { width: 1.05rem; height: 1.05rem; flex: 0 0 auto; }
    .speech-card p {
        margin: 0;
        color: rgba(255, 255, 255, 0.94);
        font-size: 0.98rem;
        line-height: 1.65;
    }
    .speech-role, .speech-org {
        color: rgba(255, 255, 255, 0.88) !important;
        font-size: 0.95rem !important;
    }
    .speech-org { margin-top: 0.1rem !important; }
    @media (max-width: 640px) {
        .speech-wrap { padding: 1.5rem 0.9rem 3rem; }
        .speech-card { padding: 0.9rem; gap: 0.7rem; }
    }
</style>
@endpush
