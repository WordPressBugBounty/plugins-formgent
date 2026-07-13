<?php defined( 'ABSPATH' ) || exit; ?>
<style>
    .formgent-card {
        background: white;
        border-radius: 16px;
        padding: 40px 32px;
        width: 100%;
        max-width: 400px;
        text-align: center;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
    }

    .formgent-success-icon,
    .formgent-processing-icon {
        margin-bottom: 16px;
    }

    .formgent-processing-icon svg {
        width: 48px;
        height: 48px;
        animation: formgent-payment-spin 1s linear infinite;
    }

    .formgent-processing-icon svg path {
        fill: #f5bb43;
    }

    .formgent-title {
        font-size: 22px;
        font-weight: 600;
        color: #141921;
        margin: 0 0 8px 0;
    }

    .formgent-subtitle {
        font-size: 16px;
        color: #747C89;
        margin-bottom: 32px;
        margin: 0;
    }

    .formgent-details {
        text-align: left;
        margin: 30px 0 24px;
        padding: 0 16px 12px;
    }

    .formgent-detail-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
    }

    .formgent-detail-row:last-child {
        border-bottom: none;
    }

    .formgent-detail-label {
        font-size: 14px;
        color: #141921;
        font-weight: 400;
    }

    .formgent-detail-value {
        font-size: 14px;
        color: #141921;
        font-weight: 600;
    }

    .formgent-amount {
        font-size: 14px;
        font-weight: 600;
        color: #141921;
    }

    .formgent-amount-after-trial {
        font-size: 12px;
        color: #747C89;
        font-weight: 400;
    }

    .formgent-status-active {
        color: #26a56c;
    }

    .formgent-status-pending {
        color: #b7791f;
    }

    .formgent-payment-method {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .formgent-done-button {
        width: 100%;
        padding: 16px;
        background: #5E53F9;
        color: white;
        border: none;
        border-radius: 2px;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .formgent-done-button:hover {
        background: #5E53F9;
    }

    .formgent-done-button:active {
        transform: translateY(1px);
    }

    .formgent-payment-notice {
        font-size: 14px;
        background: #ffeac7;
        padding: 6px 10px;
        border-left: 2px solid #f5bb43;
        margin-bottom: 20px;
    }

    .formgent-pdf-downloads {
        margin-top: 24px;
        padding: 16px;
        background: #f8fafc;
        border-radius: 8px;
        text-align: left;
    }

    .formgent-pdf-downloads__title {
        font-size: 16px;
        font-weight: 600;
        color: #141921;
        margin: 0 0 12px;
    }

    .formgent-pdf-downloads__list {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .formgent-pdf-download-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        color: #2563eb;
        font-size: 14px;
        text-decoration: none;
        transition: background 0.15s ease;
    }

    .formgent-pdf-download-link:hover {
        background: #eff6ff;
    }

    .formgent-pdf-download-link svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
    }

    @keyframes formgent-payment-spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }
</style>
