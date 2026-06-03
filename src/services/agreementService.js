import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import { supabase } from '@/lib/customSupabaseClient';
import { sendWhatsAppMessage } from '@/services/wasenderapiService';
import { formatPrice } from '@/services/sharePriceService';

/**
 * Agreement Service
 * Handles PDF generation, storage, and WhatsApp delivery of shareholder agreements
 */

/**
 * Generates PDF from HTML element
 * @param {string} elementId - ID of HTML element to convert
 * @param {string} filename - Output filename
 * @returns {Promise<Object>} { success, pdf, error }
 */
export const generatePDFFromHTML = async (elementId, filename) => {
  console.log('[AGREEMENT] Generating PDF from element:', elementId);
  console.log('[AGREEMENT] Filename:', filename);
  
  try {
    const element = document.getElementById(elementId);
    
    if (!element) {
      console.error('[AGREEMENT] Element not found:', elementId);
      throw new Error(`Element with ID "${elementId}" not found`);
    }

    console.log('[AGREEMENT] Capturing element as canvas');
    const canvas = await html2canvas(element, {
      scale: 2,
      useCORS: true,
      logging: false,
      backgroundColor: '#ffffff'
    });

    console.log('[AGREEMENT] Canvas captured, generating PDF');
    const imgData = canvas.toDataURL('image/png');
    const imgWidth = 210; // A4 width in mm
    const pageHeight = 297; // A4 height in mm
    const imgHeight = (canvas.height * imgWidth) / canvas.width;
    let heightLeft = imgHeight;
    let position = 0;

    const pdf = new jsPDF('p', 'mm', 'a4');

    // Add first page
    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
    heightLeft -= pageHeight;

    // Add additional pages if needed
    let pageCount = 1;
    while (heightLeft > 0) {
      position = heightLeft - imgHeight;
      pdf.addPage();
      pageCount++;
      pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
      heightLeft -= pageHeight;
    }

    console.log('[AGREEMENT] PDF generated successfully, pages:', pageCount);
    return { success: true, pdf };

  } catch (error) {
    console.error('[AGREEMENT] Error generating PDF:', error);
    return { success: false, error: error.message };
  }
};

/**
 * Downloads PDF to user's device
 * @param {jsPDF} pdf - PDF object
 * @param {string} filename - Download filename
 * @returns {Object} { success, error }
 */
export const downloadPDF = (pdf, filename) => {
  console.log('[AGREEMENT] Downloading PDF:', filename);
  try {
    if (!pdf) {
      throw new Error('PDF object is required');
    }

    pdf.save(filename);
    console.log('[AGREEMENT] PDF downloaded successfully');
    return { success: true };

  } catch (error) {
    console.error('[AGREEMENT] Error downloading PDF:', error);
    return { success: false, error: error.message };
  }
};

/**
 * Uploads agreement PDF to Supabase storage
 * @param {string} shareholderId - Shareholder UUID
 * @param {Blob} pdfBlob - PDF blob data
 * @param {string} filename - File name
 * @returns {Promise<Object>} { success, publicUrl, path, error }
 */
export const uploadAgreementPDF = async (shareholderId, pdfBlob, filename) => {
  console.log('[AGREEMENT] Uploading PDF to storage');
  console.log('[AGREEMENT] Shareholder ID:', shareholderId);
  console.log('[AGREEMENT] Filename:', filename);
  
  try {
    if (!shareholderId || !pdfBlob || !filename) {
      throw new Error('Shareholder ID, PDF blob, and filename are required');
    }

    const filePath = `agreements/${shareholderId}/${filename}`;
    console.log('[AGREEMENT] Upload path:', filePath);

    // Upload to storage
    const { error: uploadError } = await supabase.storage
      .from('shareholder-agreements')
      .upload(filePath, pdfBlob, {
        contentType: 'application/pdf',
        upsert: true
      });

    if (uploadError) {
      console.error('[AGREEMENT] Upload error:', uploadError);
      throw uploadError;
    }

    // Get public URL
    const { data: { publicUrl } } = supabase.storage
      .from('shareholder-agreements')
      .getPublicUrl(filePath);

    console.log('[AGREEMENT] PDF uploaded successfully');
    console.log('[AGREEMENT] Public URL:', publicUrl);
    return { success: true, publicUrl, path: filePath };

  } catch (error) {
    console.error('[AGREEMENT] Error uploading PDF:', error);
    return { success: false, error: error.message };
  }
};

/**
 * Saves PDF URL and path to shareholders table
 * @param {string} shareholderId - Shareholder UUID
 * @param {string} pdfUrl - Public URL of PDF
 * @param {string} pdfPath - Storage path of PDF
 * @returns {Promise<Object>} { success, error }
 */
export const savePDFToShareholder = async (shareholderId, pdfUrl, pdfPath) => {
  console.log('[AGREEMENT] Saving PDF info to database');
  console.log('[AGREEMENT] Shareholder ID:', shareholderId);
  
  try {
    if (!shareholderId || !pdfUrl || !pdfPath) {
      throw new Error('Shareholder ID, PDF URL, and path are required');
    }

    const { error } = await supabase
      .from('shareholders')
      .update({
        agreement_pdf_url: pdfUrl,
        agreement_pdf_path: pdfPath,
        pdf_generated_at: new Date().toISOString()
      })
      .eq('id', shareholderId);

    if (error) {
      console.error('[AGREEMENT] Database update error:', error);
      throw error;
    }

    console.log('[AGREEMENT] PDF info saved to database');
    return { success: true };

  } catch (error) {
    console.error('[AGREEMENT] Error saving PDF to database:', error);
    return { success: false, error: error.message };
  }
};

/**
 * Orchestrates complete PDF generation, upload, and database save
 * @param {string} shareholderId - Shareholder UUID
 * @param {string} elementId - HTML element ID to convert
 * @returns {Promise<Object>} { success, pdfUrl, error }
 */
export const generateAndSaveAgreementPDF = async (shareholderId, elementId) => {
  console.log('[AGREEMENT] Starting PDF generation workflow');
  console.log('[AGREEMENT] Shareholder ID:', shareholderId);
  console.log('[AGREEMENT] Element ID:', elementId);
  
  try {
    if (!shareholderId || !elementId) {
      throw new Error('Shareholder ID and element ID are required');
    }

    // Generate PDF
    const filename = `agreement-${shareholderId}-${Date.now()}.pdf`;
    console.log('[AGREEMENT] Step 1: Generating PDF');
    const { success: pdfSuccess, pdf, error: pdfError } = await generatePDFFromHTML(elementId, filename);

    if (!pdfSuccess || !pdf) {
      throw new Error(pdfError || 'Failed to generate PDF');
    }

    // Convert PDF to blob
    console.log('[AGREEMENT] Step 2: Converting to blob');
    const pdfBlob = pdf.output('blob');

    // Upload to storage
    console.log('[AGREEMENT] Step 3: Uploading to storage');
    const { success: uploadSuccess, publicUrl, path, error: uploadError } = await uploadAgreementPDF(
      shareholderId,
      pdfBlob,
      filename
    );

    if (!uploadSuccess) {
      throw new Error(uploadError || 'Failed to upload PDF');
    }

    // Save to database
    console.log('[AGREEMENT] Step 4: Saving to database');
    const { success: saveSuccess, error: saveError } = await savePDFToShareholder(
      shareholderId,
      publicUrl,
      path
    );

    if (!saveSuccess) {
      throw new Error(saveError || 'Failed to save PDF info to database');
    }

    console.log('[AGREEMENT] PDF generation workflow completed successfully');
    return { success: true, pdfUrl: publicUrl };

  } catch (error) {
    console.error('[AGREEMENT] Error in PDF generation workflow:', error);
    return { success: false, error: error.message };
  }
};

/**
 * Sends shareholder agreement via WhatsApp
 * @param {string} shareholderPhone - Phone number
 * @param {string} shareholderName - Shareholder name
 * @param {string} agreementUrl - PDF URL
 * @param {Object} investmentDetails - { approvedShares, sharePrice, totalInvestment }
 * @returns {Promise<Object>} { success, error }
 */
export const sendAgreementViaWhatsApp = async (
  shareholderPhone,
  shareholderName,
  agreementUrl,
  investmentDetails
) => {
  console.log('[AGREEMENT] Sending agreement via WhatsApp');
  console.log('[AGREEMENT] To:', shareholderPhone);
  console.log('[AGREEMENT] Name:', shareholderName);
  
  try {
    if (!shareholderPhone || !shareholderName || !agreementUrl) {
      throw new Error('Phone number, name, and agreement URL are required');
    }

    const { approvedShares, sharePrice, totalInvestment } = investmentDetails;
    console.log('[AGREEMENT] Investment details:', { approvedShares, sharePrice, totalInvestment });

    const message = `Dear ${shareholderName},

Congratulations! Your shareholder agreement has been approved.

📋 Investment Summary:
• Approved Shares: ${approvedShares || 0}
• Share Price: ${formatPrice(sharePrice || 0)}
• Total Investment: ${formatPrice(totalInvestment || 0)}

📄 Your signed shareholder agreement is ready for download:
${agreementUrl}

Please download and keep this document for your records.

Thank you for investing with Alpha Bridge Technologies.

Best regards,
Alpha Bridge Technologies Team`;

    console.log('[AGREEMENT] Sending WhatsApp message');
    const result = await sendWhatsAppMessage(
      shareholderPhone,
      message,
      {
        name: shareholderName,
        recipient_name: shareholderName
      },
      null,
      agreementUrl
    );

    if (!result.success) {
      throw new Error(result.error || 'Failed to send WhatsApp message');
    }

    console.log('[AGREEMENT] Agreement sent via WhatsApp successfully');
    return { success: true };

  } catch (error) {
    console.error('[AGREEMENT] Error sending WhatsApp message:', error);
    return { success: false, error: error.message };
  }
};

/**
 * Fetches shareholder record from database
 * @param {string} shareholderId - Shareholder UUID
 * @returns {Promise<Object>} { success, shareholder, error }
 */
export const getShareholderAgreement = async (shareholderId) => {
  console.log('[AGREEMENT] Fetching shareholder:', shareholderId);
  
  try {
    if (!shareholderId) {
      throw new Error('Shareholder ID is required');
    }

    const { data, error } = await supabase
      .from('shareholders')
      .select('*')
      .eq('id', shareholderId)
      .single();

    if (error) {
      console.error('[AGREEMENT] Fetch error:', error);
      throw error;
    }

    if (!data) {
      throw new Error('Shareholder not found');
    }

    console.log('[AGREEMENT] Shareholder fetched successfully');
    return { success: true, shareholder: data };

  } catch (error) {
    console.error('[AGREEMENT] Error fetching shareholder:', error);
    return { success: false, error: error.message };
  }
};

export default {
  generatePDFFromHTML,
  downloadPDF,
  uploadAgreementPDF,
  savePDFToShareholder,
  generateAndSaveAgreementPDF,
  sendAgreementViaWhatsApp,
  getShareholderAgreement
};