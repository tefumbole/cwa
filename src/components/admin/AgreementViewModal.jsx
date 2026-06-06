import React, { useState } from 'react';
import AgreementDocument from '@/components/admin/AgreementDocument';
import {
  generateAgreementPDFBlob,
  generateAndSaveAgreementPDF,
  downloadPDF,
  sendAgreementViaWhatsApp,
} from '@/services/agreementService';
import './AgreementViewModal.css';

const AgreementViewModal = ({ shareholder, onClose }) => {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(null);

  if (!shareholder) {
    return null;
  }

  const investmentDetails = {
    approvedShares: shareholder.shares_assigned || 0,
    totalInvestment: shareholder.investment_amount || 0,
    sharePrice: shareholder.shares_assigned > 0
      ? shareholder.investment_amount / shareholder.shares_assigned
      : 0,
  };

  const handleDownloadPDF = async () => {
    setLoading(true);
    setError(null);
    setSuccess(null);

    try {
      const { pdf, pdfBlob, filename } = await generateAgreementPDFBlob(
        shareholder.id,
        'agreement-document',
        shareholder.full_name
      );

      const downloadResult = downloadPDF(pdf, filename);
      if (!downloadResult.success) throw new Error(downloadResult.error);

      generateAndSaveAgreementPDF(shareholder.id, 'agreement-document', shareholder.full_name).catch(() => {});

      setSuccess('PDF downloaded to your device.');
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
      const element = document.getElementById('agreement-document');
      if (!element) throw new Error('Agreement document not found');

      const printWindow = window.open('', '_blank');
      if (!printWindow) throw new Error('Pop-up blocked. Allow pop-ups to print.');

      printWindow.document.write(`
        <html><head><title>Agreement-${shareholder.full_name || 'Shareholder'}</title></head>
        <body>${element.outerHTML}</body></html>
      `);
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();
    } catch (err) {
      setError(err.message || 'Failed to print agreement');
    }
  };

  const handleSendViaWhatsApp = async () => {
    setLoading(true);
    setError(null);
    setSuccess(null);

    try {
      const { pdfBlob, filename } = await generateAgreementPDFBlob(
        shareholder.id,
        'agreement-document',
        shareholder.full_name
      );

      const whatsappResult = await sendAgreementViaWhatsApp(
        shareholder.full_phone_number || shareholder.phone_number,
        shareholder.full_name,
        pdfBlob,
        filename,
        investmentDetails
      );

      if (!whatsappResult.success) {
        throw new Error(whatsappResult.error || 'Failed to send WhatsApp message');
      }

      generateAndSaveAgreementPDF(shareholder.id, 'agreement-document', shareholder.full_name).catch(() => {});

      setSuccess('Agreement sent to investor via WhatsApp.');
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
        <div className="agreement-modal-header">
          <h2>Shareholder Agreement - {shareholder.full_name}</h2>
          <button onClick={onClose} className="btn-close" disabled={loading}>×</button>
        </div>

        <div className="agreement-modal-body">
          {error && <div className="error-message">⚠️ {error}</div>}
          {success && <div className="success-message">{success}</div>}
          <AgreementDocument shareholder={shareholder} isSignedView />
        </div>

        <div className="agreement-modal-footer">
          <button onClick={handleDownloadPDF} className="btn btn-primary" disabled={loading}>
            {loading ? '⏳ Processing...' : '📥 Download PDF'}
          </button>
          <button onClick={handlePrint} className="btn btn-secondary" disabled={loading}>
            🖨️ Print
          </button>
          <button onClick={handleSendViaWhatsApp} className="btn btn-success" disabled={loading}>
            {loading ? '⏳ Sending...' : '💬 Send to Investor'}
          </button>
          <button onClick={onClose} className="btn btn-outline" disabled={loading}>Close</button>
        </div>
      </div>
    </div>
  );
};

export default AgreementViewModal;
