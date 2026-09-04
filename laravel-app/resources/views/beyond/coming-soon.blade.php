<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Site Under Construction — Catholic Women's Association Cameroon</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #d4af37;
            --gold-soft: #e8c96a;
            --ring-track: rgba(80, 62, 36, 0.72);
            --white: #ffffff;
            --muted: #d6d1c7;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: Inter, system-ui, sans-serif;
            color: var(--white);
            background: #0b0a09;
            overflow-x: hidden;
        }
        .hero {
            min-height: 100vh;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            background: #0b0a09 url('{{ $heroImage }}') center center / cover no-repeat;
            padding: 2rem 1.25rem 3.5rem;
        }
        .hero::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to top,
                rgba(8, 7, 6, 0.88) 0%,
                rgba(8, 7, 6, 0.55) 38%,
                rgba(8, 7, 6, 0.12) 68%,
                rgba(8, 7, 6, 0.05) 100%
            );
            pointer-events: none;
        }
        .content {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 920px;
            text-align: center;
        }
        .kicker {
            display: inline-block;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--gold);
            margin-bottom: 0.75rem;
        }
        h1 {
            font-family: "Playfair Display", Georgia, serif;
            font-size: clamp(1.85rem, 5vw, 3.4rem);
            font-weight: 700;
            line-height: 1.15;
            text-shadow: 0 8px 28px rgba(0, 0, 0, 0.45);
        }
        .sub {
            margin: 0.85rem auto 0;
            max-width: 36rem;
            color: var(--muted);
            font-size: clamp(0.92rem, 2vw, 1.05rem);
            line-height: 1.55;
        }
        .rings {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1.1rem 1.6rem;
            margin-top: 2.1rem;
        }
        .unit {
            width: 118px;
            text-align: center;
        }
        .dial {
            position: relative;
            width: 118px;
            height: 118px;
        }
        .dial svg {
            width: 118px;
            height: 118px;
            transform: rotate(-90deg);
        }
        .dial .track { fill: none; stroke: var(--ring-track); stroke-width: 8; }
        .dial .progress {
            fill: none;
            stroke: var(--gold);
            stroke-width: 8;
            stroke-linecap: round;
            filter: drop-shadow(0 0 6px rgba(212, 175, 55, 0.55));
            transition: stroke-dashoffset 0.35s linear;
        }
        .dial-inner {
            position: absolute;
            inset: 14px;
            border-radius: 50%;
            background: rgba(12, 10, 8, 0.62);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }
        .dial-value {
            font-family: "Playfair Display", Georgia, serif;
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }
        .dial-label {
            margin-top: 0.28rem;
            font-size: 0.62rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--muted);
            font-weight: 600;
        }
        .launched {
            display: none;
            margin-top: 1.6rem;
            font-family: "Playfair Display", Georgia, serif;
            font-size: 1.6rem;
            color: var(--gold-soft);
        }
        .launched.is-visible { display: block; }
        .rings.is-hidden { display: none; }
        @media (max-width: 560px) {
            .unit, .dial, .dial svg { width: 92px; height: 92px; }
            .dial-inner { inset: 11px; }
            .dial-value { font-size: 1.45rem; }
            .rings { gap: 0.75rem; }
        }
    </style>
</head>
<body>
    <main class="hero">
        <div class="content">
            <p class="kicker">Catholic Women's Association Cameroon</p>
            <h1>Site Under Construction</h1>
            <p class="sub">Delivery due 1 October 2026. A new home for CWACAM is on the way.</p>

            <div class="rings" id="rings" data-target="{{ $launchAtIso }}" data-window-days="{{ $windowDays }}">
                <div class="unit">
                    <div class="dial">
                        <svg viewBox="0 0 120 120" aria-hidden="true">
                            <circle class="track" cx="60" cy="60" r="52"></circle>
                            <circle class="progress" id="ring-days" cx="60" cy="60" r="52"></circle>
                        </svg>
                        <div class="dial-inner">
                            <span class="dial-value" id="val-days">--</span>
                            <span class="dial-label">Days</span>
                        </div>
                    </div>
                </div>
                <div class="unit">
                    <div class="dial">
                        <svg viewBox="0 0 120 120" aria-hidden="true">
                            <circle class="track" cx="60" cy="60" r="52"></circle>
                            <circle class="progress" id="ring-hours" cx="60" cy="60" r="52"></circle>
                        </svg>
                        <div class="dial-inner">
                            <span class="dial-value" id="val-hours">--</span>
                            <span class="dial-label">Hours</span>
                        </div>
                    </div>
                </div>
                <div class="unit">
                    <div class="dial">
                        <svg viewBox="0 0 120 120" aria-hidden="true">
                            <circle class="track" cx="60" cy="60" r="52"></circle>
                            <circle class="progress" id="ring-mins" cx="60" cy="60" r="52"></circle>
                        </svg>
                        <div class="dial-inner">
                            <span class="dial-value" id="val-mins">--</span>
                            <span class="dial-label">Mins</span>
                        </div>
                    </div>
                </div>
                <div class="unit">
                    <div class="dial">
                        <svg viewBox="0 0 120 120" aria-hidden="true">
                            <circle class="track" cx="60" cy="60" r="52"></circle>
                            <circle class="progress" id="ring-secs" cx="60" cy="60" r="52"></circle>
                        </svg>
                        <div class="dial-inner">
                            <span class="dial-value" id="val-secs">--</span>
                            <span class="dial-label">Secs</span>
                        </div>
                    </div>
                </div>
            </div>

            <p class="launched" id="launched">We have launched!</p>
        </div>
    </main>
    <script>
    (function () {
        var C = 2 * Math.PI * 52;
        var rings = document.getElementById('rings');
        var launched = document.getElementById('launched');
        var target = new Date(rings.getAttribute('data-target')).getTime();
        var windowDays = Math.max(1, parseInt(rings.getAttribute('data-window-days'), 10) || 21);
        var nodes = {
            days: { val: document.getElementById('val-days'), ring: document.getElementById('ring-days') },
            hours: { val: document.getElementById('val-hours'), ring: document.getElementById('ring-hours') },
            mins: { val: document.getElementById('val-mins'), ring: document.getElementById('ring-mins') },
            secs: { val: document.getElementById('val-secs'), ring: document.getElementById('ring-secs') }
        };
        Object.keys(nodes).forEach(function (key) {
            nodes[key].ring.style.strokeDasharray = String(C);
            nodes[key].ring.style.strokeDashoffset = String(C);
        });
        function setRing(node, value, max) {
            var progress = Math.max(0, Math.min(1, value / max));
            node.val.textContent = String(value);
            node.ring.style.strokeDashoffset = String(C * (1 - progress));
        }
        function tick() {
            var diff = target - Date.now();
            if (diff <= 0) {
                rings.classList.add('is-hidden');
                launched.classList.add('is-visible');
                return false;
            }
            var secs = Math.floor(diff / 1000);
            var days = Math.floor(secs / 86400); secs %= 86400;
            var hours = Math.floor(secs / 3600); secs %= 3600;
            var mins = Math.floor(secs / 60); secs %= 60;
            setRing(nodes.days, days, windowDays);
            setRing(nodes.hours, hours, 24);
            setRing(nodes.mins, mins, 60);
            setRing(nodes.secs, secs, 60);
            return true;
        }
        if (tick()) setInterval(tick, 1000);
    })();
    </script>
</body>
</html>
