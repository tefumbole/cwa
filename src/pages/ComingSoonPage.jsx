import React, { useEffect, useState } from 'react';
import { getTimeRemaining, LAUNCH_WINDOW_DAYS } from '@/services/countdownService';

const CIRCUMFERENCE = 2 * Math.PI * 52;
const HERO_IMAGE = '/branding/cwa-60-years-hero.webp?v=mary3';
const HERO_IMAGE_FALLBACK = '/branding/cwa-60-years-hero.jpg?v=mary3';
const LOGO_MARK = '/branding/cwa-logo-mary.png?v=mary3';

function CircularUnit({ value, label, max }) {
  const progress = Math.max(0, Math.min(1, value / max));
  const offset = CIRCUMFERENCE * (1 - progress);

  return (
    <div className="text-center">
      <div className="relative w-[62px] h-[62px] sm:w-[74px] sm:h-[74px] mx-auto">
        <svg viewBox="0 0 120 120" className="w-full h-full -rotate-90" aria-hidden="true">
          <circle cx="60" cy="60" r="52" fill="none" stroke="rgba(255,255,255,0.16)" strokeWidth="6" />
          <circle
            cx="60"
            cy="60"
            r="52"
            fill="none"
            stroke="#d4af37"
            strokeWidth="6"
            strokeLinecap="round"
            strokeDasharray={CIRCUMFERENCE}
            strokeDashoffset={offset}
            style={{ filter: 'drop-shadow(0 0 6px rgba(212, 175, 55, 0.45))', transition: 'stroke-dashoffset 0.35s linear' }}
          />
        </svg>
        <div className="absolute inset-2 flex flex-col items-center justify-center">
          <span className="font-serif text-[1.05rem] sm:text-[1.28rem] font-bold leading-none text-white">{value}</span>
          <span className="mt-0.5 text-[0.48rem] tracking-[0.14em] uppercase text-[#e8c96a] font-bold">{label}</span>
        </div>
      </div>
    </div>
  );
}

export default function ComingSoonPage() {
  const [timeLeft, setTimeLeft] = useState(() => getTimeRemaining());

  useEffect(() => {
    const timer = setInterval(() => setTimeLeft(getTimeRemaining()), 1000);
    return () => clearInterval(timer);
  }, []);

  return (
    <div className="min-h-screen flex flex-col bg-[#041830] text-white">
      <header className="flex items-center justify-between gap-4 min-h-[5.15rem] px-4 sm:px-6 bg-white shadow">
        <a href="/" className="flex items-center gap-3 no-underline">
          <img src={LOGO_MARK} alt="CWACAM" className="w-[3.35rem] h-[3.35rem] rounded-full object-cover" />
          <span>
            <span className="block font-extrabold tracking-[0.08em] text-[#0a1c3d] text-[1.22rem] leading-none">CWACAM</span>
            <span className="hidden sm:block mt-1 text-[0.52rem] font-bold tracking-[0.12em] uppercase text-gray-500 leading-tight max-w-[11.5rem]">Catholic Women's Association Cameroon</span>
          </span>
        </a>
        <a href="/donate" className="inline-flex items-center gap-2 px-4 py-2 rounded-full border border-[#d4af37] text-[#8a6d1d] text-sm font-bold no-underline">
          Donate
        </a>
      </header>

      <main
        className="relative flex-1 flex flex-col justify-center overflow-hidden bg-[#071a38] bg-cover bg-right min-h-[calc(100vh-5.15rem)]"
        style={{ backgroundImage: `linear-gradient(90deg, rgba(4,16,40,0.96) 0%, rgba(5,22,52,0.88) 24%, rgba(7,28,64,0.42) 46%, transparent 74%), url('${HERO_IMAGE}'), url('${HERO_IMAGE_FALLBACK}')` }}
        aria-label="60 years anniversary"
      >
        <div className="relative z-[2] w-full max-w-[640px] px-5 py-8 sm:pl-12">
          <p className="tracking-[0.34em] uppercase text-[0.68rem] font-bold text-white/80 mb-3">Catholic Women's Association Cameroon</p>
          <h1 className="font-serif font-bold leading-[0.92] text-[3rem] sm:text-6xl lg:text-7xl">
            60 Years
            <em className="block mt-2 italic font-semibold text-[#e8c96a] text-[0.46em] leading-tight">A New Home is on the Way</em>
          </h1>
          <p className="mt-4 max-w-md text-white/85 leading-relaxed">
            A legacy of faith, service and sisterhood.<br />
            We are preparing the official CWACAM site.<br />
            The launch countdown is running to 15 October 2026.
          </p>
          <blockquote className="mt-4 pl-4 border-l-2 border-[#d4af37] italic text-white/90 max-w-md">
            “For such a time as this, together we build.”
            <cite className="block mt-1 not-italic text-sm text-white/70">– CWACAM</cite>
          </blockquote>
          <div className="flex flex-wrap gap-3 mt-6">
            <a href="/register-now" className="inline-flex items-center gap-2 px-5 py-3 rounded-full bg-[#d4af37] text-[#3b2a08] font-bold no-underline">Notify Me at Launch →</a>
            <a href="/gallery" className="inline-flex items-center gap-2 px-5 py-3 rounded-full border border-white/55 text-white font-bold no-underline">Watch Our Story</a>
          </div>

          <div className="flex flex-wrap items-center gap-4 mt-8 px-4 py-3 rounded-[1.35rem] bg-[#06122a]/70 border border-white/10 w-max max-w-full">
            {timeLeft.isExpired ? (
              <p className="text-[#e8c96a] text-xl font-serif">We have launched!</p>
            ) : (
              <div className="flex items-center gap-2 sm:gap-3">
                <CircularUnit value={timeLeft.days} label="Days" max={LAUNCH_WINDOW_DAYS} />
                <CircularUnit value={timeLeft.hours} label="Hours" max={24} />
                <CircularUnit value={timeLeft.minutes} label="Mins" max={60} />
                <CircularUnit value={timeLeft.seconds} label="Secs" max={60} />
              </div>
            )}
            <div className="sm:pl-4 sm:border-l border-white/20">
              <small className="block text-[0.58rem] tracking-[0.16em] font-bold text-[#e8c96a] uppercase">Launch</small>
              <strong className="block mt-0.5 text-sm tracking-wide uppercase">15 October 2026</strong>
            </div>
          </div>
        </div>

        <p className="hidden lg:block absolute right-[6.5%] top-[18%] z-[2] text-right font-[cursive] text-[2rem] leading-snug pointer-events-none">
          Faith<br />Service<br />Sisterhood<br />A Brighter Tomorrow
        </p>
        <p className="hidden lg:block absolute right-[5.5%] bottom-[7.5%] z-[2] max-w-[16rem] text-right italic text-white/90 pointer-events-none">
          “For I know the plans I have for you…”
          <span className="block mt-1 not-italic text-sm">Jeremiah 29:11</span>
        </p>
      </main>
    </div>
  );
}
