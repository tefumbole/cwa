/** Canonical brand assets and contact details — single source of truth */

export const COMPANY_NAME =
  import.meta.env.VITE_COMPANY_NAME || 'Beyond Company Ltd';

export const COMPANY_NAME_SHORT = 'Beyond Company';

export const WHATSAPP_PHONE =
  import.meta.env.VITE_ADMIN_PHONE_NUMBER || '+237675321739';

/** Digits only — for wa.me links */
export const WHATSAPP_WA_ME = WHATSAPP_PHONE.replace(/\D/g, '');

export const CONTACT_PHONE_DISPLAY = '+237 675 321 739';

export const WEBSITE_URL =
  import.meta.env.VITE_SITE_URL || 'https://beyondtechworld.com';

export const WEBSITE_HOST = 'www.beyondtechworld.com';

export const CONTACT_EMAIL =
  import.meta.env.VITE_CONTACT_EMAIL || 'info@beyondtechworld.com';

export const DEFAULT_LOGO_URL =
  'https://horizons-cdn.hostinger.com/81ef3422-3855-479e-bfe8-28a4ceb0df39/a742e501955dd22251276e445b31816d.png';

export function whatsAppUrl(message) {
  const text = typeof message === 'string' ? message : '';
  return `https://wa.me/${WHATSAPP_WA_ME}?text=${encodeURIComponent(text)}`;
}

export function isValidLogoUrl(url) {
  if (!url || typeof url !== 'string') return false;
  return /^https?:\/\/.+/i.test(url.trim());
}
