import React, { useEffect, useState } from 'react';
import { getTimeRemaining, LAUNCH_WINDOW_DAYS } from '@/services/countdownService';

const CIRCUMFERENCE = 2 * Math.PI * 52;
const HERO_IMAGE = '/branding/cwa-60-years-hero.jpg';

function CircularUnit({ value, label, max }) {
  const progress = Math.max(0, Math.min(1, value / max));
  const offset = CIRCUMFERENCE * (1 - progress);

  return (
    <div className="w-[92px] sm:w-[118px] text-center">
      <div className="relative w-[92px] h-[92px] sm:w-[118px] sm:h-[118px]">
        <svg viewBox="0 0 120 120" className="w-full h-full -rotate-90" aria-hidden="true">
          <circle
            cx="60"
            cy="60"
            r="52"
            fill="none"
            stroke="rgba(80, 62, 36, 0.72)"
            strokeWidth="8"
          />
          <circle
            cx="60"
            cy="60"
            r="52"
            fill="none"
            stroke="#d4af37"
            strokeWidth="8"
            strokeLinecap="round"
            strokeDasharray={CIRCUMFERENCE}
            strokeDashoffset={offset}
            style={{ filter: 'drop-shadow(0 0 6px rgba(212, 175, 55, 0.55))', transition: 'stroke-dashoffset 0.35s linear' }}
          />
        </svg>
        <div className="absolute inset-[11px] sm:inset-[14px] rounded-full bg-black/60 backdrop-blur-[2px] flex flex-col items-center justify-center">
          <span className="font-serif text-[1.45rem] sm:text-[2rem] font-bold leading-none text-white">
            {value}
          </span>
          <span className="mt-1 text-[0.58rem] sm:text-[0.62rem] tracking-[0.16em] uppercase text-[#d6d1c7] font-semibold">
            {label}
          </span>
        </div>
      </div>
    </div>
  );
}

export default function ComingSoonPage() {
  const [timeLeft, setTimeLeft] = useState(() => getTimeRemaining());

  useEffect(() => {
    const timer = setInterval(() => {
      setTimeLeft(getTimeRemaining());
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  return (
    <main
      className="relative min-h-screen flex flex-col items-center justify-end overflow-x-hidden px-5 pb-14 pt-8"
      style={{
        backgroundColor: '#0b0a09',
        backgroundImage: `url('${HERO_IMAGE}')`,
        backgroundSize: 'cover',
        backgroundPosition: 'center center',
      }}
    >
      <div
        className="absolute inset-0 pointer-events-none"
        style={{
          background:
            'linear-gradient(to top, rgba(8, 7, 6, 0.88) 0%, rgba(8, 7, 6, 0.55) 38%, rgba(8, 7, 6, 0.12) 68%, rgba(8, 7, 6, 0.05) 100%)',
        }}
      />

      <div className="relative z-10 w-full max-w-[920px] text-center">
        <p className="inline-block tracking-[0.22em] uppercase text-xs font-semibold text-[#d4af37] mb-3">
          Catholic Women's Association Cameroon
        </p>
        <h1
          className="text-white font-bold leading-tight text-[1.85rem] sm:text-5xl"
          style={{ fontFamily: '"Playfair Display", Georgia, serif', textShadow: '0 8px 28px rgba(0, 0, 0, 0.45)' }}
        >
          Site Under Construction
        </h1>
        <p className="mt-3 mx-auto max-w-xl text-[#d6d1c7] text-sm sm:text-base leading-relaxed">
          Delivery due 1 October 2026. A new home for CWACAM is on the way.
        </p>

        {timeLeft.isExpired ? (
          <p
            className="mt-8 text-[#e8c96a] text-2xl"
            style={{ fontFamily: '"Playfair Display", Georgia, serif' }}
          >
            We have launched!
          </p>
        ) : (
          <div className="flex justify-center flex-wrap gap-3 sm:gap-6 mt-8">
            <CircularUnit value={timeLeft.days} label="Days" max={LAUNCH_WINDOW_DAYS} />
            <CircularUnit value={timeLeft.hours} label="Hours" max={24} />
            <CircularUnit value={timeLeft.minutes} label="Mins" max={60} />
            <CircularUnit value={timeLeft.seconds} label="Secs" max={60} />
          </div>
        )}
      </div>
    </main>
  );
}
