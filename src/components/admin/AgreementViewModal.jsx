import React, { useState } from 'react';
import AgreementDocument from '@/components/admin/AgreementDocument';
import {
  generateAndSaveAgreementPDF,
  sendAgreementViaWhatsApp
} from '@/services/agreementService';
import './AgreementViewModal.css';

const AgreementViewModal = ({ shareholder, onClose }) => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);

  if (!shareholder) {
    return null;
  }

  const handleDownloadPDF = async () => {
    setLoading(true);
    setError(null);
    setSuccess(null);

    try {
      console.log('AgreementViewModal: Generating and saving PDF...');

      const result = await generateAndSaveAgreementPDF(
        shareholder.id,
        'agreement-document'
      );

      if (!result.success) {
        throw new Error(result.error || 'Failed to generate PDF');
      }

      setSuccess('✓ PDF generated and saved successfully!');
      
      // Close modal after short delay
      setTimeout(() => {
        onClose();
      }, 2000);

    } catch (err) {
      console.error('AgreementViewModal: Download error:', err);
      setError(err.message || 'Failed to generate PDF');
    } finally {
      setLoading(false);
    }
  };

  const handlePrint = () => {
    try {
      setError(null);
      setSuccess(null);

      console.log('AgreementViewModal: Opening print preview...');

      // Get agreement document element
      const element = document.getElementById('agreement-document');
      
      if (!element) {
        throw new Error('Agreement document not found');
      }

      // Store original body content
      const originalContent = document.body.innerHTML;
      const originalTitle = document.title;

      // Temporarily replace body with agreement document
      document.body.innerHTML = element.outerHTML;
      document.title = `Agreement-${shareholder.full_name || 'Shareholder'}`;

      // Open print dialog
      window.print();

      // Restore original content
      document.body.innerHTML = originalContent;
      document.title = originalTitle;

      // Reattach event listeners (React will handle this on next render)
      window.location.reload();

    } catch (err) {
      console.error('AgreementViewModal: Print error:', err);
      setError(err.message || 'Failed to print agreement');
    }
  };

  const handleSendViaWhatsApp = async () => {
    setLoading(true);
    setError(null);
    setSuccess(null);

    try {
      console.log('AgreementViewModal: Preparing to send via WhatsApp...');

      // Check if PDF already exists
      let pdfUrl = shareholder.agreement_pdf_url;

      // If no PDF exists, generate it first
      if (!pdfUrl) {
        console.log('AgreementViewModal: No PDF found, generating...');
        
        const pdfResult = await generateAndSaveAgreementPDF(
          shareholder.id,
          'agreement-document'
        );

        if (!pdfResult.success) {
          throw new Error(pdfResult.error || 'Failed to generate PDF');
        }

        pdfUrl = pdfResult.pdfUrl;
      }

      // Prepare investment details
      const approvedShares = shareholder.shares_assigned || 0;
      const totalInvestment = shareholder.investment_amount || 0;
      const pricePerShare = approvedShares > 0 ? totalInvestment / approvedShares : 0;

      const investmentDetails = {
        approvedShares,
        sharePrice: pricePerShare,
        totalInvestment
      };

      // Send via WhatsApp
      console.log('AgreementViewModal: Sending WhatsApp message...');
      
      const whatsappResult = await sendAgreementViaWhatsApp(
        shareholder.full_phone_number || shareholder.phone_number,
        shareholder.full_name,
        pdfUrl,
        investmentDetails
      );

      if (!whatsappResult.success) {
        throw new Error(whatsappResult.error || 'Failed to send WhatsApp message');
      }

      setSuccess('✓ Agreement sent to investor via WhatsApp successfully!');
      
      // Close modal after delay
      setTimeout(() => {
        onClose();
      }, 3000);

    } catch (err) {
      console.error('AgreementViewModal: WhatsApp send error:', err);
      setError(err.message || 'Failed to send agreement via WhatsApp');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="agreement-modal-overlay" onClick={onClose}>
      <div className="agreement-modal-content" onClick={(e) => e.stopPropagation()}>
        {/* Header */}
        <div className="agreement-modal-header">
          <h2>Shareholder Agreement - {shareholder.full_name}</h2>
          <button
            onClick={onClose}
            className="btn-close"
            disabled={loading}
          >
            ×
          </button>
        </div>

        {/* Body - Agreement Document */}
        <div className="agreement-modal-body">
          {error && (
            <div className="error-message">
              ⚠️ {error}
            </div>
          )}

          {success && (
            <div className="success-message">
              {success}
            </div>
          )}

          <AgreementDocument shareholder={shareholder} />
        </div>

        {/* Footer - Action Buttons */}
        <div className="agreement-modal-footer">
          <button
            onClick={handleDownloadPDF}
            className="btn btn-primary"
            disabled={loading}
          >
            {loading ? '⏳ Processing...' : '📥 Download PDF'}
          </button>

          <button
            onClick={handlePrint}
            className="btn btn-secondary"
            disabled={loading}
          >
            🖨️ Print
          </button>

          <button
            onClick={handleSendViaWhatsApp}
            className="btn btn-success"
            disabled={loading}
          >
            {loading ? '⏳ Sending...' : '💬 Send to Investor'}
          </button>

          <button
            onClick={onClose}
            className="btn btn-outline"
            disabled={loading}
          >
            Close
          </button>
        </div>
      </div>
    </div>
  );
};

export default AgreementViewModal;