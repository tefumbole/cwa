/**
 * Time remaining until the CWACAM public launch.
 * Default: 1 October 2026, 00:00 Africa/Douala (UTC+1).
 */

const DEFAULT_LAUNCH = '2026-10-01T00:00:00+01:00';

const WINDOW_DAYS = Math.max(
  1,
  Number(import.meta.env.VITE_LAUNCH_WINDOW_DAYS || 21)
);

function getTargetMs() {
  const raw = import.meta.env.VITE_LAUNCH_AT || DEFAULT_LAUNCH;
  const parsed = new Date(raw).getTime();
  return Number.isNaN(parsed) ? new Date(DEFAULT_LAUNCH).getTime() : parsed;
}

export const TARGET_DATE = getTargetMs();
export const LAUNCH_WINDOW_DAYS = WINDOW_DAYS;

export const getTimeRemaining = () => {
  const now = Date.now();
  const total = TARGET_DATE - now;

  if (total <= 0) {
    return {
      total: 0,
      days: 0,
      hours: 0,
      minutes: 0,
      seconds: 0,
      isExpired: true,
      windowDays: WINDOW_DAYS,
    };
  }

  const seconds = Math.floor((total / 1000) % 60);
  const minutes = Math.floor((total / 1000 / 60) % 60);
  const hours = Math.floor((total / (1000 * 60 * 60)) % 24);
  const days = Math.floor(total / (1000 * 60 * 60 * 24));

  return {
    total,
    days,
    hours,
    minutes,
    seconds,
    isExpired: false,
    windowDays: WINDOW_DAYS,
  };
};
