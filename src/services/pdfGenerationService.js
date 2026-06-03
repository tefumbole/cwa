import { supabase } from '@/lib/customSupabaseClient';
import QRCode from 'qrcode';
import JsBarcode from 'jsbarcode';
import html2pdf from 'html2pdf.js';
import { format } from 'date-fns';

const BUCKET_NAME = 'system-assets';
const PDF_FOLDER = 'message-pdfs';

export const generateQRCode = async (text) => {
  try {
    return await QRCode.toDataURL(text, { errorCorrectionLevel: 'H' });
  } catch (error) {
    console.error('Error generating QR code:', error);
    return null;
  }
};

export const generateBarcode = (text) => {
  try {
    const canvas = document.createElement('canvas');
    JsBarcode(canvas, text, {
      format: 'CODE128',
      displayValue: true,
      fontSize: 14,
      height: 40,
      margin: 10
    });
    return canvas.toDataURL('image/png');
  } catch (error) {
    console.error('Error generating Barcode:', error);
    return null;
  }
};

export const personalizeContent = (template, recipientData) => {
  if (!template) return '';
  let content = template;
  const today = format(new Date(), 'dd MMMM yyyy');

  const replacements = {
    '{name}': recipientData.name || 'Recipient',
    '{email}': recipientData.email || '',
    '{phone}': recipientData.phone || '',
    '{date}': today
  };

  for (const [key, value] of Object.entries(replacements)) {
    // Replace all instances of the variable (using global regex)
    const regex = new RegExp(key.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1"), 'g');
    content = content.replace(regex, value);
  }

  return content;
};

export const generateMessagePDFHTML = async (messageData, recipientData, settings = {}) => {
  const personalizedBody = personalizeContent(messageData.body, recipientData);
  const qrCodeDataUrl = recipientData.verification_url ? await generateQRCode(recipientData.verification_url) : null;
  const barcodeDataUrl = recipientData.reference_code ? generateBarcode(recipientData.reference_code) : null;

  const headerHtml = settings.header_url 
    ? `<div style="text-align: center; margin-bottom: 20px;"><img src="${settings.header_url}" style="max-height: 100px; max-width: 100%;" alt="Header" /></div>` 
    : `<div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #003D82; padding-bottom: 10px;"><h1 style="color: #003D82; margin: 0;">${settings.org_name || 'Alpha Bridge Technologies Ltd'}</h1></div>`;

  const footerHtml = settings.footer_url
    ? `<div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;"><img src="${settings.footer_url}" style="max-height: 60px; max-width: 100%;" alt="Footer" /></div>`
    : `<div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666;"><p>${settings.org_name || 'Alpha Bridge Technologies Ltd'} | System Generated Document</p></div>`;

  return `
    <div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.6; padding: 40px; max-width: 800px; margin: 0 auto; background: white;">
      ${headerHtml}
      
      <div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
          <p style="margin: 0; font-weight: bold;">Date: ${format(new Date(), 'dd MMM yyyy')}</p>
          <p style="margin: 5px 0 0 0; font-weight: bold;">Ref: ${recipientData.reference_code}</p>
        </div>
        ${barcodeDataUrl ? `<div><img src="${barcodeDataUrl}" alt="Barcode" style="height: 60px;" /></div>` : ''}
      </div>

      <div style="margin-bottom: 30px;">
        <h2 style="margin: 0 0 10px 0; color: #111;">${messageData.subject}</h2>
      </div>

      <div style="margin-bottom: 40px; white-space: pre-wrap;">
        ${personalizedBody}
      </div>

      <div style="display: flex; justify-content: flex-end; align-items: flex-end; margin-top: 40px;">
        ${qrCodeDataUrl ? `
        <div style="text-align: center; background: #f9f9f9; padding: 10px; border-radius: 8px; border: 1px solid #eee;">
          <img src="${qrCodeDataUrl}" alt="Verification QR Code" style="width: 100px; height: 100px; display: block; margin: 0 auto;" />
          <p style="margin: 5px 0 0 0; font-size: 10px; color: #555;">Scan to Verify</p>
        </div>
        ` : ''}
      </div>

      ${footerHtml}
    </div>
  `;
};

export const generateAndUploadPDF = async (htmlContent, fileName) => {
  try {
    // Create a temporary element to hold the HTML
    const element = document.createElement('div');
    element.innerHTML = htmlContent;
    
    // Configure html2pdf
    const opt = {
      margin:       0,
      filename:     fileName,
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2, useCORS: true },
      jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
    };

    // Generate blob
    const pdfBlob = await html2pdf().set(opt).from(element).output('blob');
    
    // Upload to Supabase Storage
    const filePath = `${PDF_FOLDER}/${fileName}`;
    const { error: uploadError } = await supabase.storage
      .from(BUCKET_NAME)
      .upload(filePath, pdfBlob, {
        contentType: 'application/pdf',
        upsert: true
      });

    if (uploadError) throw uploadError;

    // Get public URL
    const { data: { publicUrl } } = supabase.storage
      .from(BUCKET_NAME)
      .getPublicUrl(filePath);

    return publicUrl;
  } catch (error) {
    console.error('Error generating or uploading PDF:', error);
    throw error;
  }
};

export const generatePDFsForMessage = async (messageId, messageData, recipients) => {
  try {
    // Fetch settings for headers/footers
    const { data: settingsData } = await supabase
      .from('message_settings')
      .select('*')
      .limit(1)
      .single();
      
    const settings = settingsData || {};
    let successCount = 0;
    let failCount = 0;

    // Process sequentially to not overload browser memory
    for (const recipient of recipients) {
      try {
        const htmlContent = await generateMessagePDFHTML(messageData, recipient, settings);
        const fileName = `${recipient.reference_code}.pdf`;
        const pdfUrl = await generateAndUploadPDF(htmlContent, fileName);

        // Update recipient record with pdf_url
        if (pdfUrl && recipient.id) {
          await supabase
            .from('message_recipients')
            .update({ pdf_url: pdfUrl })
            .eq('id', recipient.id);
          successCount++;
        } else {
            failCount++;
        }
      } catch (err) {
        console.error(`Failed to generate PDF for recipient ${recipient.name}:`, err);
        failCount++;
      }
    }

    return { successCount, failCount, total: recipients.length };
  } catch (error) {
    console.error('Error in batch PDF generation:', error);
    throw error;
  }
};