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

    .formgent-success-icon{
        margin-bottom: 16px;
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
</style>