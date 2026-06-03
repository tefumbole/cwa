/**
 * Phone Number Service
 * Utility functions for phone number manipulation
 */

/**
 * Extract phone number without country code
 * @param {string} countryCode - Country code with + (e.g., '+237')
 * @param {string} phoneNumber - Phone number without country code
 * @returns {string} Clean phone number suitable for use as password
 */
export const extractPhoneWithoutCode = (countryCode, phoneNumber) => {
  if (!phoneNumber) return '';
  
  // Remove all non-digit characters
  let cleaned = phoneNumber.replace(/\D/g, '');
  
  // Remove leading zeros
  cleaned = cleaned.replace(/^0+/, '');
  
  // If country code is somehow included in the phone number, remove it
  if (countryCode) {
    const codeDigits = countryCode.replace(/\D/g, '');
    if (cleaned.startsWith(codeDigits)) {
      cleaned = cleaned.substring(codeDigits.length);
    }
  }
  
  return cleaned;
};

/**
 * Combine country code and phone number
 * @param {string} countryCode - Country code with + (e.g., '+237')
 * @param {string} phoneNumber - Phone number without country code
 * @returns {string} Full phone number with country code
 */
export const combinePhoneNumber = (countryCode, phoneNumber) => {
  if (!phoneNumber) return '';
  
  const cleanedPhone = extractPhoneWithoutCode(countryCode, phoneNumber);
  const cleanedCode = countryCode.replace(/\D/g, '');
  
  return `+${cleanedCode}${cleanedPhone}`;
};

/**
 * Validate phone number format
 * @param {string} phoneNumber - Phone number to validate
 * @returns {boolean} True if valid
 */
export const validatePhoneFormat = (phoneNumber) => {
  if (!phoneNumber) return false;
  
  const digitsOnly = phoneNumber.replace(/\D/g, '');
  return digitsOnly.length >= 6 && digitsOnly.length <= 15;
};

export default {
  extractPhoneWithoutCode,
  combinePhoneNumber,
  validatePhoneFormat
};