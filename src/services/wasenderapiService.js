import { format } from 'date-fns';

/**
 * WaSender API Service Integration
 * Handles sending WhatsApp messages via WaSender API.
 */

const API_KEY = import.meta.env.VITE_WASENDER_API_KEY;
const DEVICE = import.meta.env.VITE_WASENDER_PHONE;
const API_URL = import.meta.env.VITE_WASENDER_API_URL || "https://wasenderapi.com/api/send-message";

// Utility to mask the API key for secure logging
export const maskKey = (key) => key && key.length > 8 ? `${key.substring(0, 4)}***${key.slice(-4)}` : 'MISSING';

/**
 * Validates and formats phone numbers to ensure +250 (Rwanda) format.
 * Defaults to prepending +250 if local format is detected.
 */
export const formatPhoneNumber = (phone) => {
  if (!phone) return '';
  let cleaned = String(phone).replace(/\D/g, ''); // Remove non-digits
  
  if (cleaned.startsWith('00')) {
      cleaned = cleaned.substring(2);
  }
  
  // Enforce Rwanda format (+250)
  if (cleaned.length === 9 && (cleaned.startsWith('7') || cleaned.startsWith('8') || cleaned.startsWith('9'))) {
      cleaned = '250' + cleaned;
  } else if (cleaned.length === 10 && cleaned.startsWith('07')) {
      cleaned = '250' + cleaned.substring(1);
  } else if (cleaned.length === 9 && cleaned.startsWith('6')) {
      // Catch for Cameroon (+237) legacy handling just in case
      cleaned = '237' + cleaned;
  }
  
  // Ensure plus prefix
  if (!cleaned.startsWith('+')) {
      return '+' + cleaned;
  }
  return cleaned;
};

const isValidPhoneNumber = (phone) => !!(phone && phone.length >= 8);

const logApiInteraction = (type, success, details) => {
  const timestamp = new Date().toISOString();
  const sanitizedDetails = typeof details === "object" ? { ...details } : details;
  console.log(`[WASENDER] [${timestamp}] [${type}] Success: ${success}`, sanitizedDetails);
};

const safeJson = async (res) => {
  const t = await res.text();
  try {
    return t ? JSON.parse(t) : {};
  } catch {
    return { raw: t };
  }
};

const personalizeMessage = (template, recipientData, referenceCode, pdfUrl) => {
  if (!template) return '';
  let content = String(template);
  const today = format(new Date(), 'dd MMMM yyyy');

  const replacements = {
    '{name}': recipientData?.name || recipientData?.recipient_name || 'Recipient',
    '{email}': recipientData?.email || recipientData?.recipient_email || '',
    '{phone}': recipientData?.phone || recipientData?.recipient_phone || '',
    '{date}': today
  };

  for (const [key, value] of Object.entries(replacements)) {
    const regex = new RegExp(key.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1"), 'g');
    content = content.replace(regex, value);
  }

  if (referenceCode || pdfUrl) {
      content += '\n\n---\n';
      if (referenceCode) content += `Ref: ${referenceCode}\n`;
      if (pdfUrl) content += `Document: ${pdfUrl}\n`;
  }

  return content;
};

/**
 * Sends a text message via WhatsApp using WaSender API
 */
export const sendWhatsAppMessage = async (phoneNumber, messageTemplate, recipientData = {}, referenceCode = null, pdfUrl = null) => {
  const formattedPhone = formatPhoneNumber(phoneNumber);
  
  console.log('[WASENDER_DEBUG] Configuration Check:', {
    hasApiKey: !!API_KEY,
    maskedApiKey: maskKey(API_KEY),
    apiUrl: API_URL,
    senderPhone: DEVICE,
    recipientOriginal: phoneNumber,
    recipientFormatted: formattedPhone,
  });

  if (!isValidPhoneNumber(formattedPhone)) {
    console.error('[WASENDER] Invalid phone number format:', formattedPhone);
    return { success: false, error: "Invalid phone number format." };
  }

  if (!API_KEY || !DEVICE || !API_URL) {
    console.error("[WASENDER] Configuration missing - Verify .env variables.");
    return { success: false, error: "Configuration missing - Check environment variables" };
  }

  const personalizedMessage = personalizeMessage(messageTemplate, recipientData, referenceCode, pdfUrl);

  // Strictly follow required payload structure
  const payload = {
    api_key: API_KEY,
    sender: DEVICE,
    number: formattedPhone,
    message: String(personalizedMessage ?? "")
  };

  try {
    logApiInteraction("REQUEST_SEND", null, { 
      to: formattedPhone, 
      msgLength: payload.message.length,
      sender: payload.sender
    });

    const response = await fetch(API_URL, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json"
      },
      body: JSON.stringify(payload),
    });

    const data = await safeJson(response);

    if (!response.ok || data.status === "error" || data.success === false) {
      const errorMsg = data?.message || data?.error || response.statusText || "Request failed";
      console.error('[WASENDER] API Error:', response.status, errorMsg);
      throw new Error(`API Error: ${response.status} ${errorMsg}`);
    }

    logApiInteraction("RESPONSE_SUCCESS", true, { messageId: data.message_id || "unknown" });
    return { success: true, data };
  } catch (error) {
    console.error('[WASENDER] Send failed:', error.message);
    logApiInteraction("ERROR_SEND", false, { error: error?.message });
    return { success: false, error: error?.message || "Network error or API unreachable" };
  }
};

/**
 * Sends a WhatsApp message with an image attachment
 * (Adjusted payload mapping depending on WaSender's image endpoint requirements, mapping media URL to appropriate field if needed)
 */
export const sendWhatsAppMessageWithImage = async (phoneNumber, messageTemplate, imageUrl, recipientData = {}, referenceCode = null, pdfUrl = null) => {
  const formattedPhone = formatPhoneNumber(phoneNumber);
  
  console.log('[WASENDER_DEBUG] Configuration Check (Image):', {
    hasApiKey: !!API_KEY,
    maskedApiKey: maskKey(API_KEY),
    senderPhone: DEVICE,
    recipientFormatted: formattedPhone,
    hasImage: !!imageUrl
  });

  if (!isValidPhoneNumber(formattedPhone)) {
    return { success: false, error: "Invalid phone number format." };
  }

  const personalizedMessage = personalizeMessage(messageTemplate, recipientData, referenceCode, pdfUrl);

  // Assumes API accepts `url` or `media` based on WaSender documentation. 
  // Modifying endpoint URL dynamically if standard endpoint differs for media
  const imageEndpoint = API_URL.replace('/send-message', '/send-image');
  
  const payload = {
    api_key: API_KEY,
    sender: DEVICE,
    number: formattedPhone,
    message: String(personalizedMessage ?? ""),
    url: String(imageUrl ?? "")
  };

  try {
    logApiInteraction("REQUEST_IMAGE_SEND", null, { to: formattedPhone });

    const response = await fetch(imageEndpoint, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    });

    const data = await safeJson(response);

    if (!response.ok || data.status === "error" || data.success === false) {
      throw new Error(data?.message || data?.error || "Unknown API error");
    }

    logApiInteraction("RESPONSE_IMAGE_SUCCESS", true, { messageId: data.message_id || "unknown" });
    return { success: true, data };
  } catch (error) {
    logApiInteraction("ERROR_IMAGE_SEND", false, { error: error?.message });
    return { success: false, error: error?.message || "Network error" };
  }
};