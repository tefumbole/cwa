<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $siteLogoUrl = \App\Support\SiteBrand::logoUrl($general_setting ?? null);
        $siteTitle = \App\Support\SiteBrand::siteTitle($general_setting ?? null);
        $webUser = Auth::guard('web')->user();
        $beyondUser = Auth::guard('beyond')->user();
        $headerUser = $webUser ?: $beyondUser;
        $isAdminSession = (bool) $webUser;
        $headerName = $headerUser ? $headerUser->name : '';
        $headerRole = $isAdminSession
            ? __('cwa.nav.administrator')
            : (optional($beyondUser)->role ? strtoupper(str_replace('_', ' ', $beyondUser->role)) : __('cwa.nav.user'));
        $headerInitial = $headerName !== '' ? mb_strtoupper(mb_substr($headerName, 0, 1)) : 'U';
        $shortName = \Illuminate\Support\Str::limit($headerName, 18, '…');
        $isHome = request()->is('/');
    @endphp
    <title>@yield('title', $siteTitle) | {{ $siteTitle }}</title>
    <meta name="description" content="@yield('meta_description', 'Catholic Women\'s Association Cameroon — faith, service and sisterhood.')">
    <link rel="icon" href="{{ $siteLogoUrl }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { blue: '#003D82', dark: '#002855', light: '#0066CC', gold: '#D4AF37', navy: '#1a1a2e' },
                    },
                },
            },
        };
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,700;1,9..144,600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
        :root {
            --cwa-paper: #F6F3EC;
            --cwa-ink: #1A1F2E;
            --cwa-gold: #D4AF37;
        }
        @keyframes floaty { 0%,100% { transform: translateY(0); opacity:.4 } 50% { transform: translateY(-20px); opacity:.9 } }
        .floaty { animation: floaty 4s ease-in-out infinite; }
        [x-cloak] { display:none !important; }

        @keyframes navLogoSpin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        /* Gold ↔ Silver metallic shift (no white plate / circle) */
        @keyframes navLogoMetal {
            0%, 100% {
                filter: sepia(1) saturate(4.2) hue-rotate(2deg) brightness(1.12) contrast(1.05)
                    drop-shadow(0 0 8px rgba(212,175,55,.7));
            }
            50% {
                filter: grayscale(1) brightness(1.45) contrast(1.15) saturate(0.2)
                    drop-shadow(0 0 8px rgba(220,220,230,.65));
            }
        }
        .nav-logo-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-right: .75rem;
            background: transparent;
            border: 0;
            padding: 0;
            box-shadow: none;
        }
        .nav-logo-spin {
            width: 2.75rem;
            height: 2.75rem;
            object-fit: contain;
            background: transparent;
            border-radius: 0;
            animation: navLogoSpin 7s linear infinite, navLogoMetal 5s ease-in-out infinite;
        }
        @media (min-width: 768px) {
            .nav-logo-spin { width: 3.25rem; height: 3.25rem; }
        }
        @media (min-width: 1024px) {
            .nav-logo-spin { width: 3.5rem; height: 3.5rem; }
        }
        .cwa-foot {
            background:
                linear-gradient(90deg, rgba(212,175,55,0.22), transparent 18%, transparent 82%, rgba(212,175,55,0.18)),
                linear-gradient(180deg, #0a2a5c 0%, #003D82 55%, #071a38 100%);
            color: rgba(255,255,255,0.88);
            border-top-left-radius: 1.35rem;
            border-top-right-radius: 1.35rem;
            box-shadow: 0 -8px 24px rgba(7, 26, 56, 0.18);
            position: relative;
        }
        .cwa-foot::before {
            content: "";
            position: absolute;
            left: 8%;
            right: 8%;
            top: 0;
            height: 3px;
            border-radius: 999px;
            background: linear-gradient(90deg, transparent, #D4AF37 20%, #f0d56a 50%, #D4AF37 80%, transparent);
        }
        .cwa-foot-inner {
            display: grid;
            grid-template-columns: auto 1fr 1fr 1.15fr;
            gap: 1.1rem 1.6rem;
            align-items: start;
            max-width: 72rem;
            margin: 0 auto;
            padding: 0.7rem 1.15rem 0.35rem;
        }
        .cwa-foot-qr {
            width: 3.35rem;
            height: 3.35rem;
            padding: 0.22rem;
            background: #fff;
            border-radius: 0.4rem;
            border: 2px solid #D4AF37;
        }
        .cwa-foot-qr img { width: 100%; height: 100%; display: block; }
        .cwa-foot h3 {
            margin: 0 0 0.28rem;
            color: #E8C96A;
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }
        .cwa-foot ul { list-style: none; margin: 0; padding: 0; }
        .cwa-foot li { margin: 0 0 0.12rem; }
        .cwa-foot a, .cwa-foot p {
            color: rgba(255,255,255,0.86);
            font-size: 0.72rem;
            line-height: 1.3;
            text-decoration: none;
            margin: 0;
        }
        .cwa-foot a:hover { color: #E8C96A; }
        .cwa-foot-row {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin: 0 0 0.14rem;
            font-size: 0.72rem;
            color: rgba(255,255,255,0.86);
        }
        .cwa-foot-row i { width: 0.85rem; height: 0.85rem; color: #D4AF37; flex-shrink: 0; }
        .cwa-foot-copy {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 0.15rem 0;
            padding: 0.28rem 1rem 0.4rem;
            color: #D4AF37;
            font-size: 0.68rem;
            text-align: center;
        }
        .cwa-foot-copy span { padding: 0 0.7rem; }
        .cwa-foot-copy span + span { border-left: 1px solid rgba(212,175,55,0.4); }
        .cwa-foot-copy a { color: #D4AF37; font-weight: 600; }
        .cwa-foot-copy a:hover { color: #f0d56a; }
        @media (max-width: 800px) {
            .cwa-foot-inner { grid-template-columns: auto 1fr 1fr; }
            .cwa-foot-contact { grid-column: 2 / -1; }
        }
        @media (max-width: 640px) {
            .cwa-foot-inner { grid-template-columns: 1fr 1fr; padding: 0.65rem 0.9rem 0.3rem; }
            .cwa-foot-qr { display: none; }
            .cwa-foot-copy { flex-direction: column; }
            .cwa-foot-copy span { padding: 0; }
            .cwa-foot-copy span + span { border-left: 0; }
        }
        body.cwa-home .cwa-foot {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 40;
        }
    </style>
    @stack('head')
</head>
<body class="bg-[#F6F3EC] text-[#1A1F2E] flex flex-col min-h-screen {{ !empty($isHome) ? 'cwa-home' : '' }}">

@php
    $navLinks = \App\Support\SiteMenu::landingNavLinks();
    $currentUrl = url()->current();
@endphp

<header class="site-header sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-stone-200/80" x-data="{ open: false, userMenu: false }" @keydown.escape.window="userMenu = false">
    <div class="w-full flex items-center justify-between h-14 sm:h-16 pl-1 pr-3 sm:pl-2 sm:pr-6 lg:pl-3 lg:pr-8">
        <a href="{{ url('/') }}" class="nav-logo-link" aria-label="{{ $siteTitle }} home">
            <img src="{{ $siteLogoUrl }}" alt="{{ $siteTitle }}" class="nav-logo-spin">
        </a>

        <nav class="hidden lg:flex items-center gap-x-7 xl:gap-x-10 flex-1 justify-center min-w-0">
            @foreach ($navLinks as $link)
                @php $active = \App\Support\SiteMenu::navLinkIsActive($link, $currentUrl); @endphp
                <a href="{{ $link['url'] }}"
                   class="text-[1.05rem] xl:text-[1.15rem] font-semibold transition-colors duration-300 whitespace-nowrap
                      @if($active) text-brand-blue border-b-2 border-brand-gold pb-1
                      @elseif(!empty($link['special'])) text-brand-blue hover:text-brand-gold font-bold
                      @else text-stone-600 hover:text-brand-blue @endif">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="hidden lg:flex items-center gap-2 xl:gap-3 shrink-0">
            @include('beyond.partials.lang_switch', ['variant' => 'light'])
            <a href="{{ route('beyond.donate') }}" class="hidden xl:inline-flex items-center gap-1.5 border border-stone-300 text-stone-700 hover:border-brand-gold hover:text-brand-blue font-semibold rounded-full px-3 py-1.5 text-sm">
                {{ __('cwa.nav.donate') }}
            </a>
            <a href="{{ route('beyond.membership') }}" class="inline-flex items-center gap-1.5 bg-brand-gold text-brand-blue hover:bg-[#c4a030] font-bold rounded-full px-3.5 py-1.5 text-sm">
                {{ __('cwa.nav.join') }}
            </a>

            <a href="tel:+237675321739" class="text-stone-500 hover:text-brand-blue transition-colors" title="{{ __('cwa.nav.call') }}">
                <i data-lucide="phone" class="w-5 h-5"></i>
            </a>
            <a href="https://mail.hostinger.com" target="_blank" rel="noopener" class="text-stone-500 hover:text-brand-blue transition-colors" title="{{ __('cwa.nav.webmail') }}">
                <i data-lucide="mail" class="w-5 h-5"></i>
            </a>

            @if ($headerUser)
                <div class="relative" @click.outside="userMenu = false">
                    <button type="button" @click="userMenu = !userMenu"
                            class="flex items-center gap-2.5 pl-1 pr-1 py-1 rounded-md hover:bg-white/10 transition-colors text-left">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 border-brand-gold bg-gradient-to-br from-brand-gold to-brand-dark text-brand-blue font-bold text-lg">
                            {{ $headerInitial }}
                        </span>
                        <span class="hidden xl:flex flex-col leading-tight min-w-0">
                            <span class="text-stone-800 font-semibold text-sm truncate max-w-[140px]">{{ $shortName }}</span>
                            <span class="text-brand-blue text-[11px] font-bold tracking-wide uppercase">{{ $headerRole }}</span>
                        </span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-stone-400 shrink-0"></i>
                    </button>
                    <div x-show="userMenu" x-cloak x-transition
                         class="absolute right-0 mt-2 w-56 rounded-lg bg-white shadow-xl border border-gray-100 py-1 z-50">
                        <div class="px-4 py-2.5 text-sm font-bold text-gray-800">{{ __('cwa.nav.account') }}</div>
                        <div class="border-t border-gray-100"></div>
                        @if ($isAdminSession)
                            <a href="{{ url('/admin') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-800 hover:bg-gray-50">
                                <i data-lucide="layout-grid" class="w-4 h-4 text-gray-700"></i> {{ __('cwa.nav.admin') }}
                            </a>
                            <a href="{{ url('/') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-800 hover:bg-gray-50">
                                <i data-lucide="home" class="w-4 h-4 text-gray-700"></i> {{ __('cwa.nav.home_page') }}
                            </a>
                        @else
                            <a href="{{ url('/user/profile') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-800 hover:bg-gray-50">
                                <i data-lucide="user" class="w-4 h-4 text-gray-700"></i> {{ __('cwa.nav.profile') }}
                            </a>
                        @endif
                        <form method="POST" action="{{ $isAdminSession ? route('logout') : route('beyond.logout') }}" @click.stop>
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                                <i data-lucide="log-out" class="w-4 h-4"></i> {{ __('cwa.nav.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ url('/login') }}" class="border border-stone-300 text-stone-700 hover:border-brand-blue hover:text-brand-blue font-medium transition-all rounded-full px-4 py-2 flex items-center gap-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i> {{ __('cwa.nav.login') }}
                </a>
            @endif
        </div>

        <button @click="open = !open" class="lg:hidden text-stone-700 hover:text-brand-blue transition-colors">
            <i data-lucide="menu" class="w-6 h-6" x-show="!open"></i>
            <i data-lucide="x" class="w-6 h-6" x-show="open" x-cloak></i>
        </button>
    </div>

    <div x-show="open" x-cloak class="lg:hidden pb-4 px-4 bg-white border-t border-stone-200">
        <nav class="flex flex-col space-y-3 pt-4">
            @foreach ($navLinks as $link)
                <a href="{{ $link['url'] }}" class="text-lg font-medium {{ !empty($link['special']) ? 'text-brand-blue' : 'text-stone-700 hover:text-brand-blue' }}">{{ $link['label'] }}</a>
            @endforeach
            <a href="{{ route('beyond.membership') }}" class="text-lg font-bold text-brand-blue">{{ __('cwa.nav.join') }}</a>
            <a href="{{ route('beyond.donate') }}" class="text-lg font-medium text-stone-700 hover:text-brand-blue">{{ __('cwa.nav.donate') }}</a>
            <a href="{{ url('/documents') }}" class="text-lg font-medium text-stone-700 hover:text-brand-blue">{{ __('cwa.nav.resources') }}</a>
            <div class="pt-2">@include('beyond.partials.lang_switch', ['variant' => 'light'])</div>
            <div class="pt-3 border-t border-stone-200 space-y-2">
                @if ($headerUser)
                    <div class="flex items-center gap-3 px-1 py-2">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-brand-gold bg-brand-gold text-brand-blue font-bold">{{ $headerInitial }}</span>
                        <div>
                            <div class="text-stone-800 font-semibold text-sm">{{ $headerName }}</div>
                            <div class="text-brand-blue text-xs font-bold uppercase">{{ $headerRole }}</div>
                        </div>
                    </div>
                    @if ($isAdminSession)
                        <a href="{{ url('/admin') }}" class="flex items-center justify-center gap-2 w-full py-2 rounded bg-brand-gold text-brand-blue font-bold">{{ __('cwa.nav.admin') }}</a>
                        <a href="{{ url('/') }}" class="flex items-center justify-center gap-2 w-full py-2 rounded border border-white/20 text-white">{{ __('cwa.nav.home_page') }}</a>
                    @else
                        <a href="{{ url('/user/profile') }}" class="flex items-center justify-center gap-2 w-full py-2 rounded bg-brand-gold text-brand-blue font-bold">{{ __('cwa.nav.profile') }}</a>
                    @endif
                    <form method="POST" action="{{ $isAdminSession ? route('logout') : route('beyond.logout') }}">
                        @csrf
                        <button type="submit" class="w-full py-2 rounded border border-red-400/50 text-red-300">{{ __('cwa.nav.logout') }}</button>
                    </form>
                @else
                    <a href="{{ url('/login') }}" class="flex items-center justify-center gap-2 w-full py-2 rounded border border-brand-gold text-brand-gold font-medium">
                        <i data-lucide="log-in" class="w-5 h-5"></i> {{ __('cwa.nav.login') }}
                    </a>
                @endif
            </div>
        </nav>
    </div>
</header>

<main class="flex-1">
    @yield('content')
</main>

@php
    $footEmail = \App\Support\SiteContent::text('contact.email', 'info@cwacam.org');
    $footWeb = \App\Support\SiteContent::text('contact.website', 'www.cwacam.org');
    $footQr = '';
    try {
        $footQr = \DNS2D::getBarcodePNG(url('/'), 'QRCODE');
    } catch (\Throwable $e) {
        $footQr = '';
    }
@endphp
<footer class="cwa-foot mt-auto">
    <div class="cwa-foot-inner">
        <div class="cwa-foot-qr" aria-hidden="true">
            @if ($footQr)
                <img src="data:image/png;base64,{{ $footQr }}" alt="">
            @endif
        </div>
        <div>
            <h3>{{ __('cwa.footer.association') }}</h3>
            <ul>
                <li><a href="{{ url('/about') }}">{{ __('cwa.nav.about') }}</a></li>
                <li><a href="{{ route('beyond.membership') }}">{{ __('cwa.nav.membership') }}</a></li>
                <li><a href="{{ route('beyond.membership') }}">{{ __('cwa.nav.join') }}</a></li>
            </ul>
        </div>
        <div>
            <h3>{{ __('cwa.footer.community') }}</h3>
            <ul>
                <li><a href="{{ url('/events') }}">{{ __('cwa.nav.events') }}</a></li>
                <li><a href="{{ url('/gallery') }}">{{ __('cwa.nav.gallery') }}</a></li>
                <li><a href="{{ route('beyond.donate') }}">{{ __('cwa.nav.donate') }}</a></li>
            </ul>
        </div>
        <div class="cwa-foot-contact">
            <h3>{{ __('cwa.footer.contact') }}</h3>
            <p class="cwa-foot-row"><i data-lucide="mail"></i><a href="mailto:{{ $footEmail }}">{{ $footEmail }}</a></p>
            <p class="cwa-foot-row"><i data-lucide="phone"></i><a href="https://wa.me/237675321739" target="_blank" rel="noopener">+237 675-321-739</a></p>
            <p class="cwa-foot-row"><i data-lucide="globe"></i><a href="{{ url('/') }}">{{ $footWeb }}</a></p>
            <p class="cwa-foot-row"><i data-lucide="map-pin"></i>{{ __('cwa.footer.seat') }}</p>
        </div>
    </div>
    <div class="cwa-foot-copy">
        <span>© {{ date('Y') }} CWA Cameroon. {{ __('cwa.footer.rights') }}</span>
        <span>{{ __('cwa.footer.developed') }} Sr. Engr. Tefu R. Mbole</span>
        <span><a href="https://wa.me/237675321739" target="_blank" rel="noopener">+237 675-321-739</a></span>
        <span>{{ \App\Support\AppVersion::erp() }}</span>
    </div>
</footer>

<a href="https://wa.me/237675321739" target="_blank" rel="noopener"
   class="cwa-wa fixed bottom-20 right-5 z-50 bg-[#25D366] hover:bg-[#1EBE57] text-white rounded-full p-3 shadow-xl hover:shadow-2xl transition-all flex items-center justify-center"
   title="{{ __('cwa.footer.whatsapp') }}">
    <i data-lucide="message-circle" class="w-6 h-6"></i>
</a>

<script src="https://unpkg.com/lucide@latest"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script>
    window.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });
    document.addEventListener('alpine:initialized', () => { if (window.lucide) lucide.createIcons(); });
</script>
@stack('scripts')
@include('components.whatsapp_phone_script')
<script>
(function () {
    if (window.__eventCountdownInit) return;
    window.__eventCountdownInit = true;
    function pad(n) { return n < 10 ? '0' + n : String(n); }
    function bindCountdown(el) {
        if (el.__countdownBound) return;
        el.__countdownBound = true;
        var targetIso = el.getAttribute('data-target');
        if (!targetIso) return;
        var hideAfter = el.getAttribute('data-hide-after') === '1';
        var doneMsg = el.querySelector('[data-done]');
        var units = el.querySelector('[data-units]');
        var target = new Date(targetIso).getTime();
        if (isNaN(target)) return;
        function tick() {
            var diff = target - Date.now();
            if (diff <= 0) {
                if (units) units.classList.add('hidden');
                if (doneMsg) doneMsg.classList.remove('hidden');
                if (hideAfter) setTimeout(function () { el.style.display = 'none'; }, 8000);
                return false;
            }
            var secs = Math.floor(diff / 1000);
            var days = Math.floor(secs / 86400); secs %= 86400;
            var hours = Math.floor(secs / 3600); secs %= 3600;
            var mins = Math.floor(secs / 60); secs %= 60;
            var d = el.querySelector('.cd-days');
            var h = el.querySelector('.cd-hours');
            var m = el.querySelector('.cd-mins');
            var s = el.querySelector('.cd-secs');
            if (d) d.textContent = days;
            if (h) h.textContent = pad(hours);
            if (m) m.textContent = pad(mins);
            if (s) s.textContent = pad(secs);
            return true;
        }
        if (tick()) setInterval(tick, 1000);
    }
    document.querySelectorAll('[data-countdown]').forEach(bindCountdown);
})();
</script>
</body>
</html>
