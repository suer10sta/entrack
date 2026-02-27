@php
    // Force the application name used in this template to DalyTrac
    $appName = 'DalyTrac';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset | {{ $appName }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Basic reset */
        body, html {
            margin: 0;
            padding: 0;
            background-color: #f5f7fb;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1f2933;
        }

        a {
            color: #2563eb;
            text-decoration: none;
        }

        /* Container */
        .wrapper {
            width: 100%;
            background-color: #f5f7fb;
            padding: 40px 0;
        }

        .email-container {
            max-width: 640px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        /* Header */
        .email-header {
            text-align: center;
            padding: 32px 32px 16px 32px;
            background: #0f172a;
        }

        .email-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .email-logo img {
            max-width: 140px;
            height: auto;
            display: block;
        }

        .email-header-title {
            margin-top: 12px;
            font-size: 18px;
            font-weight: 600;
            color: #e5e7eb;
        }

        /* Body */
        .email-body {
            padding: 32px;
        }

        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .body-text {
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 16px 0;
            color: #4b5563;
        }

        /* Button */
        .button-wrapper {
            text-align: center;
            margin: 32px 0 24px 0;
        }

        .primary-button {
            display: inline-block;
            padding: 12px 28px;
            border-radius: 999px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-decoration: none;
        }

        .primary-button:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
        }

        /* Footer */
        .email-footer {
            border-top: 1px solid #e5e7eb;
            padding: 20px 32px 24px 32px;
            text-align: left;
        }

        .footer-text {
            font-size: 12px;
            line-height: 1.6;
            color: #6b7280;
            margin: 0 0 8px 0;
        }

        .small-muted {
            font-size: 11px;
            color: #9ca3af;
        }

        .sub-copy {
            font-size: 12px;
            line-height: 1.6;
            color: #6b7280;
            margin-top: 16px;
        }

        .sub-copy a {
            word-break: break-all;
        }

        @media (max-width: 600px) {
            .email-container {
                border-radius: 0;
            }

            .email-body,
            .email-footer {
                padding: 20px 16px;
            }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="email-container">
        <div class="email-header">
            <div class="email-logo">
                <img src="{{ asset('img/dalytraclogo.png') }}" alt="{{ $appName }} Logo">
            </div>
            <div class="email-header-title">
                {{ $appName }} Password Reset
            </div>
        </div>

        <div class="email-body">
            <p class="greeting">Hello{{ isset($userName) ? ' ' . e($userName) : '' }},</p>

            <p class="body-text">
                You’re receiving this email because we received a password reset request for your {{ $appName }} account.
            </p>

            <div class="button-wrapper">
                <a href="{{ $resetUrl }}" class="primary-button" target="_blank" rel="noopener">
                    Reset Password
                </a>
            </div>

            <p class="body-text">
                This password reset link will expire in {{ $expireMinutes }} minutes.
            </p>

            <p class="body-text">
                If you did not request a password reset, you can safely ignore this email and no changes will be made to your account.
            </p>
        </div>

        <div class="email-footer">
            <p class="footer-text">
                Regards,<br>
                The {{ $appName }} Team
            </p>

            <p class="sub-copy">
                If you’re having trouble clicking the <strong>“Reset Password”</strong> button, copy and paste the URL below into your web browser:<br>
                <a href="{{ $resetUrl }}" target="_blank" rel="noopener">{{ $resetUrl }}</a>
            </p>

            <p class="small-muted">
                © {{ date('Y') }} {{ $appName }}. All rights reserved.
            </p>
        </div>
    </div>
</div>
</body>
</html>


