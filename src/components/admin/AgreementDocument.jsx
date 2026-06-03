import React from 'react';
import { formatPrice } from '@/services/sharePriceService';
import './AgreementDocument.css';

const AgreementDocument = ({ shareholder }) => {
  if (!shareholder) {
    return <div className="agreement-document">No shareholder data provided</div>;
  }

  const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
      return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    } catch {
      return 'N/A';
    }
  };

  const approvedShares = shareholder.shares_assigned || 0;
  const totalInvestment = shareholder.investment_amount || 0;
  const pricePerShare = approvedShares > 0 ? totalInvestment / approvedShares : 0;

  return (
    <div className="agreement-document" id="agreement-document">
      {/* Header */}
      <div className="agreement-header">
        <div className="company-logo">
          <h1>Alpha Bridge Technologies</h1>
          <p className="company-tagline">Building Tomorrow's Solutions Today</p>
        </div>
        <h2 className="agreement-title">SHAREHOLDER AGREEMENT</h2>
      </div>

      {/* Investor Details Section */}
      <div className="agreement-section">
        <h3>Investor Details</h3>
        <div className="details-grid">
          <div className="detail-item">
            <span className="detail-label">Full Name:</span>
            <span className="detail-value">{shareholder.full_name || 'N/A'}</span>
          </div>
          <div className="detail-item">
            <span className="detail-label">Email Address:</span>
            <span className="detail-value">{shareholder.email || 'N/A'}</span>
          </div>
          <div className="detail-item">
            <span className="detail-label">Phone Number:</span>
            <span className="detail-value">{shareholder.full_phone_number || 'N/A'}</span>
          </div>
          <div className="detail-item">
            <span className="detail-label">Country:</span>
            <span className="detail-value">{shareholder.country_code || 'N/A'}</span>
          </div>
          <div className="detail-item">
            <span className="detail-label">Address:</span>
            <span className="detail-value">{shareholder.address || 'N/A'}</span>
          </div>
        </div>
      </div>

      {/* Investment Details Section */}
      <div className="agreement-section">
        <h3>Investment Details</h3>
        <div className="investment-grid">
          <div className="investment-item">
            <span className="investment-label">Approved Shares</span>
            <span className="investment-value">{approvedShares}</span>
          </div>
          <div className="investment-item">
            <span className="investment-label">Share Price</span>
            <span className="investment-value">{formatPrice(pricePerShare)}</span>
          </div>
          <div className="investment-item">
            <span className="investment-label">Total Investment</span>
            <span className="investment-value">{formatPrice(totalInvestment)}</span>
          </div>
          <div className="investment-item">
            <span className="investment-label">Payment Status</span>
            <span className="investment-value status-badge">
              {shareholder.payment_status || 'pending'}
            </span>
          </div>
        </div>
      </div>

      {/* Terms & Conditions Section */}
      <div className="agreement-section">
        <h3>Terms & Conditions</h3>
        <div className="terms-list">
          <div className="term-item">
            <span className="term-number">1.</span>
            <div className="term-content">
              <strong>About Alpha Bridge Technologies:</strong> The Company is a technology solutions provider incorporated under the laws of Cameroon, specializing in software development, IT consulting, and digital transformation services.
            </div>
          </div>

          <div className="term-item">
            <span className="term-number">2.</span>
            <div className="term-content">
              <strong>Share Price & Value:</strong> The share price stated in this agreement represents the current valuation at the time of approval. The shareholder acknowledges that share values may fluctuate based on company performance and market conditions.
            </div>
          </div>

          <div className="term-item">
            <span className="term-number">3.</span>
            <div className="term-content">
              <strong>Shareholder Rights:</strong> The shareholder is entitled to voting rights proportional to their shareholding, access to annual financial statements, participation in shareholder meetings, and distribution of dividends as declared by the Board of Directors.
            </div>
          </div>

          <div className="term-item">
            <span className="term-number">4.</span>
            <div className="term-content">
              <strong>Dividends:</strong> Dividends will be distributed at the discretion of the Board of Directors based on company profitability and cash flow requirements. No guaranteed dividend schedule is implied.
            </div>
          </div>

          <div className="term-item">
            <span className="term-number">5.</span>
            <div className="term-content">
              <strong>Share Transfers:</strong> Shares may be transferred subject to approval by the Board of Directors and compliance with applicable securities laws. Existing shareholders maintain a right of first refusal on any proposed transfers.
            </div>
          </div>

          <div className="term-item">
            <span className="term-number">6.</span>
            <div className="term-content">
              <strong>Confidentiality:</strong> The shareholder agrees to maintain confidentiality regarding proprietary company information, trade secrets, and non-public financial data obtained through their shareholding position.
            </div>
          </div>

          <div className="term-item">
            <span className="term-number">7.</span>
            <div className="term-content">
              <strong>Dispute Resolution:</strong> Any disputes arising from this agreement shall be resolved through arbitration under the laws of Cameroon. Both parties agree to good faith negotiation before pursuing formal legal action.
            </div>
          </div>

          <div className="term-item">
            <span className="term-number">8.</span>
            <div className="term-content">
              <strong>Agreement Termination:</strong> This agreement remains in effect until shares are sold, transferred, or the company is dissolved. Termination does not relieve parties of obligations incurred during the agreement period.
            </div>
          </div>
        </div>
      </div>

      {/* Digital Signature Section */}
      {shareholder.signature && (
        <div className="agreement-section">
          <h3>Digital Signature</h3>
          <div className="signature-box">
            <p className="signature-label">Investor's Signature</p>
            <img
              src={shareholder.signature}
              alt="Shareholder signature"
              className="signature-image"
            />
            <p className="signature-name">{shareholder.full_name}</p>
            <p className="signature-date">
              Signed on: {formatDate(shareholder.agreement_signed_at)}
            </p>
          </div>
        </div>
      )}

      {/* Approval Information Section */}
      <div className="agreement-section">
        <h3>Approval Information</h3>
        <div className="approval-grid">
          <div className="approval-item">
            <span className="approval-label">Approval Date</span>
            <span className="approval-value">{formatDate(shareholder.approved_at)}</span>
          </div>
          <div className="approval-item">
            <span className="approval-label">Status</span>
            <span className="approval-value status-approved">
              {shareholder.status === 'approved' ? '✓ APPROVED' : shareholder.status}
            </span>
          </div>
          <div className="approval-item">
            <span className="approval-label">Agreement Accepted</span>
            <span className="approval-value">
              {shareholder.agreement_signed_at ? '✓ Yes' : '✗ No'}
            </span>
          </div>
        </div>
      </div>

      {/* Footer */}
      <div className="agreement-footer">
        <p>
          This document is confidential and intended solely for the shareholder named above.
        </p>
        <p>
          <strong>Alpha Bridge Technologies</strong><br />
          Douala, Cameroon<br />
          Email: info@alpha-bridge.net | Phone: +237 675 321 739
        </p>
        <p className="footer-date">
          Generated on: {formatDate(new Date().toISOString())}
        </p>
      </div>
    </div>
  );
};

export default AgreementDocument;