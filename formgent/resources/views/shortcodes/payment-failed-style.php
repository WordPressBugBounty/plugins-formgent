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

    .formgent-error-icon {
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
        margin: 0 0 30px;
    }

    .formgent-try-again-button {
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
        margin-bottom: 12px;
    }

    .formgent-try-again-button:hover {
        background: #5856eb;
    }

    .formgent-try-again-button:active {
        transform: translateY(1px);
    }

    .formgent-go-home-button {
        width: 100%;
        padding: 16px;
        background: transparent;
        color: #6366f1;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s;
        display: block;
        text-decoration: none;
        box-sizing: border-box;
    }

    .formgent-go-home-button:hover {
        background: #f8f9ff;
    }

    .formgent-go-home-button:active {
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