@extends('beyond.layout')

@section('title', __('cwa.speech.title'))
@section('meta_description', __('cwa.speech.meta'))

@section('content')
<div class="speech-page">
    <div class="speech-wrap">
        <div class="speech-toolbar">
            <p class="speech-kicker">{{ __('cwa.speech.kicker') }}</p>
            <a class="speech-download" href="{{ route('beyond.speech.pdf') }}">{{ __('cwa.speech.download') }}</a>
        </div>

        <article class="speech-letter">
            <header class="speech-letter-head">
                <img src="{{ $portraitUrl }}" alt="{{ __('cwa.speech.sign_name') }}">
                <div>
                    <h1>{{ __('cwa.speech.sign_name') }}</h1>
                </div>
            </header>

            <div class="speech-letter-body">
                @foreach (__('cwa.speech.sections') as $section)
                    <p>{{ $section['body'] }}</p>
                @endforeach
            </div>

            <footer class="speech-letter-foot">
                <div>
                    <p class="speech-signed">{{ __('cwa.speech.sign_name') }}</p>
                    <p>{{ __('cwa.speech.sign_role') }}</p>
                    <p>{{ __('cwa.speech.sign_org') }}</p>
                </div>
                @if(!empty($signUrl))
                    <img src="{{ $signUrl }}" alt="{{ __('cwa.speech.sign_name') }}">
                @endif
            </footer>
        </article>
    </div>
</div>
@endsection

@push('head')
<style>
    .speech-page {
        background:
            radial-gradient(circle at 12% 8%, rgba(212, 175, 55, 0.16), transparent 28%),
            radial-gradient(circle at 88% 0%, rgba(0, 61, 130, 0.06), transparent 24%),
            #f6f1e6;
        min-height: 70vh;
    }
    .speech-wrap { max-width: 46rem; margin: 0 auto; padding: 2.2rem 1rem 4rem; }
    .speech-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .speech-kicker {
        margin: 0;
        color: #8a6d1d;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        font-size: 0.72rem;
        font-weight: 800;
    }
    .speech-download {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.7rem 1.15rem;
        border-radius: 999px;
        background: #D4AF37;
        color: #3b2a08;
        font-weight: 800;
        text-decoration: none;
        box-shadow: 0 8px 18px rgba(212, 175, 55, 0.28);
    }
    .speech-download:hover { background: #e0c05a; color: #3b2a08; }
    .speech-letter {
        background: #fbf8f2;
        border: 1px solid rgba(138, 109, 29, 0.16);
        border-radius: 18px;
        padding: 1.6rem 1.35rem 1.4rem;
        box-shadow: 0 18px 40px rgba(62, 46, 16, 0.06);
    }
    .speech-letter-head {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        padding-bottom: 0.9rem;
        border-bottom: 1px solid rgba(90, 74, 50, 0.18);
    }
    .speech-letter-head img {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        object-fit: cover;
        flex: 0 0 auto;
    }
    .speech-letter-head h1 {
        margin: 0;
        color: #2c2416;
        font-family: Fraunces, Georgia, serif;
        font-size: 1.35rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-weight: 700;
    }
    .speech-letter-body { padding: 1.15rem 0 0.4rem; }
    .speech-letter-body p {
        margin: 0 0 1rem;
        color: #3d3426;
        font-family: Georgia, "Times New Roman", serif;
        font-size: 1.05rem;
        line-height: 1.7;
    }
    .speech-letter-foot {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 1rem;
        margin-top: 0.4rem;
        padding-top: 0.9rem;
        border-top: 1px solid rgba(90, 74, 50, 0.18);
    }
    .speech-letter-foot p { margin: 0; color: #5c513f; font-size: 0.95rem; }
    .speech-signed {
        font-family: Fraunces, Georgia, serif;
        font-style: italic;
        font-size: 1.15rem !important;
        color: #2c2416 !important;
    }
    .speech-letter-foot img {
        height: 72px;
        width: auto;
        max-width: 180px;
        object-fit: contain;
    }
    @media (max-width: 640px) {
        .speech-wrap { padding: 1.3rem 0.85rem 3rem; }
        .speech-toolbar { align-items: flex-start; flex-direction: column; }
        .speech-letter { padding: 1.15rem 0.95rem; }
        .speech-letter-head h1 { font-size: 1.05rem; }
        .speech-letter-body p { font-size: 1rem; }
    }
</style>
@endpush
