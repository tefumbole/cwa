<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('cwa.speech.title') }}</title>
    @php
        $letterhead_flow = true;
        $use_system_letterhead = true;
    @endphp
    @include('pdf.partials._letter_branded_styles')
    <style>
        .speech-pdf h1 {
            margin: 0 0 4px;
            font-size: 18px;
            color: #0b3f90;
        }
        .speech-pdf .who {
            margin: 0 0 14px;
            color: #5c513f;
            font-size: 12px;
        }
        .speech-pdf p.body {
            margin: 0 0 12px;
            font-size: 12.5px;
            line-height: 1.55;
            text-align: justify;
        }
        .speech-sign {
            margin-top: 18px;
            page-break-inside: avoid;
        }
        .speech-sign img {
            height: 64px;
            width: auto;
        }
        .speech-sign .name {
            margin: 4px 0 0;
            font-weight: bold;
            font-size: 13px;
        }
        .speech-sign .role {
            margin: 0;
            font-size: 12px;
        }
    </style>
</head>
<body>
@include('pdf.partials._letter_branded_open')
<div class="speech-pdf">
    <h1>{{ __('cwa.speech.title') }}</h1>
    <p class="who">{{ __('cwa.speech.sign_name') }} · {{ __('cwa.speech.sign_role') }} · {{ __('cwa.speech.date') }}</p>
    @foreach (__('cwa.speech.sections') as $section)
        <p class="body">{{ $section['body'] }}</p>
    @endforeach
    <div class="speech-sign">
        @if(!empty($signPath))
            <img src="{{ \App\Support\Letterhead::pdfImage($signPath, 500) }}" alt="">
        @endif
        <p class="name">{{ __('cwa.speech.sign_name') }}</p>
        <p class="role">{{ __('cwa.speech.sign_role') }}</p>
        <p class="role">{{ __('cwa.speech.sign_org') }}</p>
    </div>
</div>
@include('pdf.partials._letter_branded_close')
</body>
</html>
