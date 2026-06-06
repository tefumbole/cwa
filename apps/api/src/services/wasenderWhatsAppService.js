/**
 * WasenderAPI — server-side (Manukeza / New Vision pattern).
 * Keeps API key off the browser.
 */

function getApiKey() {
  return process.env.WASENDER_API_KEY || '';
}

function getBaseUrl() {
  return normalizeBaseUrl(process.env.WASENDER_BASE_URL || 'https://wasenderapi.com/api');
}

function getDefaultCountry() {
  return (process.env.WASENDER_DEFAULT_COUNTRY || 'CM').toUpperCase();
}

const COUNTRY_CODES = {
  RW: '+250',
  CM: '+237',
  UG: '+256',
  KE: '+254',
  TZ: '+255',
};

function normalizeBaseUrl(url) {
  const trimmed = String(url || 'https://wasenderapi.com').trim().replace(/\/$/, '');
  if (trimmed.endsWith('/send-message')) {
    return trimmed.replace(/\/send-message$/, '');
  }
  return trimmed.endsWith('/api') ? trimmed : `${trimmed}/api`;
}

export function formatPhoneNumber(phone, countryCode = getDefaultCountry()) {
  if (!phone) return '';

  let cleaned = String(phone).trim().replace(/[\s\-()]/g, '');

  if (cleaned.startsWith('00')) cleaned = cleaned.substring(2);

  if (cleaned.startsWith('+')) {
    return /^\+\d{8,15}$/.test(cleaned) ? cleaned : '';
  }

  const prefix = COUNTRY_CODES[countryCode.toUpperCase()];
  if (!prefix) return '';

  const digits = prefix.substring(1);
  if (cleaned.startsWith(digits)) return `+${cleaned}`;
  if (cleaned.startsWith('0')) cleaned = cleaned.substring(1);

  const formatted = `${prefix}${cleaned}`;
  return /^\+\d{8,15}$/.test(formatted) ? formatted : '';
}

export function isWasenderConfigured() {
  const key = getApiKey();
  return Boolean(key && !key.startsWith('your_'));
}

async function wasenderRequest(method, url, { json, body, contentType } = {}) {
  const headers = {
    Authorization: `Bearer ${getApiKey()}`,
    Accept: 'application/json',
  };
  const options = { method, headers };

  if (json !== undefined) {
    headers['Content-Type'] = 'application/json';
    options.body = JSON.stringify(json);
  } else if (body !== undefined) {
    if (contentType) headers['Content-Type'] = contentType;
    options.body = body;
  }

  try {
    const response = await fetch(url, options);
    const text = await response.text();
    let parsed = null;
    try {
      parsed = text ? JSON.parse(text) : null;
    } catch {
      parsed = { raw: text };
    }

    return { ok: response.ok, status: response.status, json: parsed, body: text };
  } catch (err) {
    const hint = err?.cause?.code === 'EHOSTUNREACH' || err?.code === 'EHOSTUNREACH'
      ? 'Cannot reach Wasender API. Check internet connection, firewall, and WASENDER_BASE_URL in apps/api/.env.'
      : err.message;
    console.error('[WASENDER] Network error:', err);
    return {
      ok: false,
      status: 0,
      json: { success: false, message: hint },
      body: hint,
      networkError: true,
    };
  }
}

function buildResult(success, phone, body, error) {
  const msgId = body?.data?.msgId || null;
  return {
    success,
    status: success ? 'sent' : 'failed',
    error: error || null,
    phone_number: phone,
    messageSid: msgId,
    provider_sid: msgId,
    data: body,
  };
}

export async function sendTextMessage(toPhone, text, messageType = 'text') {
  if (!isWasenderConfigured()) {
    return buildResult(false, toPhone, null, 'Wasender API key missing. Set WASENDER_API_KEY in apps/api/.env');
  }

  const to = formatPhoneNumber(toPhone);
  if (!to) {
    return buildResult(false, toPhone, null, 'Invalid phone number format.');
  }

  const response = await wasenderRequest('POST', `${getBaseUrl()}/send-message`, {
    json: { to, text: String(text ?? '') },
  });

  const body = response.json || { raw: response.body };

  if (response.networkError) {
    return buildResult(false, to, body, body?.message || 'Could not reach Wasender API');
  }

  if (response.ok && body?.success) {
    console.log(`[WASENDER] ${messageType} sent to ${to}: ${body?.data?.msgId}`);
    return buildResult(true, to, body, null);
  }

  const error = body?.message || body?.error || response.body || 'Send failed';
  console.error('[WASENDER] Send failed:', error);
  return buildResult(false, to, body, typeof error === 'string' ? error : 'Send failed');
}

export async function sendOtp(toPhone, otp, context = null) {
  let message = `Your Alpha Bridge verification code is: *${otp}*`;
  if (context) message += `\n\n${context}`;
  message += '\n\nThis code expires in 10 minutes. Do not share it with anyone.';
  return sendTextMessage(toPhone, message, 'otp');
}

export async function uploadBufferToWasender(buffer, mimeType = 'application/pdf') {
  if (!isWasenderConfigured()) {
    return { success: false, error: 'Wasender API key missing. Set WASENDER_API_KEY in apps/api/.env', public_url: null };
  }

  const response = await wasenderRequest('POST', `${getBaseUrl()}/upload`, {
    body: buffer,
    contentType: mimeType,
  });

  const payload = response.json || { raw: response.body };
  const publicUrl =
    payload?.publicUrl ||
    payload?.public_url ||
    payload?.data?.publicUrl ||
    payload?.data?.public_url ||
    payload?.url ||
    null;

  if (response.ok && publicUrl) {
    return { success: true, public_url: publicUrl, error: null };
  }

  const error = payload?.message || payload?.error || response.body || 'Upload failed.';
  console.error('[WASENDER] Upload failed:', error);
  return {
    success: false,
    public_url: null,
    error: typeof error === 'string' ? error : 'Upload failed.',
  };
}

function sleep(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

/**
 * New Vision pattern: text first, pause, then document via Wasender public URL.
 */
export async function sendTextThenDocumentBuffer(
  toPhone,
  text,
  buffer,
  fileName = 'document.pdf',
  mimeType = 'application/pdf'
) {
  if (!isWasenderConfigured()) {
    return buildResult(false, toPhone, null, 'Wasender API key missing. Set WASENDER_API_KEY in apps/api/.env');
  }

  const to = formatPhoneNumber(toPhone);
  if (!to) return buildResult(false, toPhone, null, 'Invalid phone number format.');

  const textResult = await sendTextMessage(to, text, 'document-intro');
  if (!textResult.success) return textResult;

  await sleep(3000);

  const upload = await uploadBufferToWasender(buffer, mimeType);
  if (!upload.success || !upload.public_url) {
    return buildResult(false, to, null, upload.error || 'Failed to upload document to WhatsApp');
  }

  return sendDocumentMessage(to, upload.public_url, null, fileName);
}

export async function sendDocumentMessage(toPhone, documentUrl, text = null, fileName = null) {
  if (!isWasenderConfigured()) {
    return buildResult(false, toPhone, null, 'Wasender API key missing. Set WASENDER_API_KEY in apps/api/.env');
  }

  const to = formatPhoneNumber(toPhone);
  if (!to) return buildResult(false, toPhone, null, 'Invalid phone number format.');

  const payload = { to, documentUrl };
  if (text) payload.text = text;
  if (fileName) payload.fileName = fileName;

  const response = await wasenderRequest('POST', `${getBaseUrl()}/send-message`, { json: payload });
  const body = response.json || {};

  if (response.ok && body?.success) {
    return buildResult(true, to, body, null);
  }

  return buildResult(false, to, body, body?.message || 'Send failed');
}

export async function sendImageMessage(toPhone, imageUrl, text = null) {
  const to = formatPhoneNumber(toPhone);
  if (!to) return buildResult(false, toPhone, null, 'Invalid phone number format.');

  const payload = { to, imageUrl };
  if (text) payload.text = text;

  const response = await wasenderRequest('POST', `${getBaseUrl()}/send-message`, { json: payload });
  const body = response.json || {};

  if (response.ok && body?.success) {
    return buildResult(true, to, body, null);
  }

  return buildResult(false, to, body, body?.message || 'Send failed');
}
