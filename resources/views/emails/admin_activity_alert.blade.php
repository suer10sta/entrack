@php
    $appName = 'DalyTrac';
    $emailTitle = $title ?? 'New Activity Alert';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $emailTitle }} | {{ $appName }}</title>
    <style>
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

        .email-header {
            text-align: center;
            padding: 28px 28px 16px 28px;
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
            margin-top: 10px;
            font-size: 18px;
            font-weight: 600;
            color: #e5e7eb;
        }

        .email-body {
            padding: 28px;
        }

        .alert-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 8px 0;
            color: #111827;
        }

        .body-text {
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 18px 0;
            color: #4b5563;
        }

        .details-box {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
            padding: 16px 18px;
        }

        .details-title {
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6b7280;
            margin: 0 0 10px 0;
        }

        .details-list {
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 13px;
            color: #374151;
        }

        .details-list li {
            padding: 6px 0;
            border-top: 1px solid #e5e7eb;
        }

        .details-list li:first-child {
            border-top: none;
        }

        .details-label {
            font-weight: 600;
        }

        .email-footer {
            border-top: 1px solid #e5e7eb;
            padding: 18px 28px 22px 28px;
            text-align: left;
        }

        .footer-text {
            font-size: 12px;
            line-height: 1.6;
            color: #6b7280;
            margin: 0 0 4px 0;
        }

        .small-muted {
            font-size: 11px;
            color: #9ca3af;
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
                {{ $appName }} – Admin Activity Alert
            </div>
        </div>

        <div class="email-body">
            <p class="alert-title">{{ $emailTitle }}</p>

            @if(!empty($body))
                <p class="body-text">{{ $body }}</p>
            @endif

            @if(!empty($meta) && is_array($meta))
                <div class="details-box">
                    <p class="details-title">Details</p>
                    <ul class="details-list">
                        @foreach($meta as $key => $value)
                            <li>
                                <span class="details-label">
                                    {{ ucfirst(str_replace('_', ' ', $key)) }}:
                                </span>
                                <span>
                                    {{ is_scalar($value) ? $value : json_encode($value) }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="email-footer">
            <p class="footer-text">
                You are receiving this email because you are configured as an admin contact for {{ $appName }}.
            </p>
            <p class="small-muted">
                © {{ date('Y') }} {{ $appName }}. All rights reserved.
            </p>
        </div>
    </div>
</div>
</body>
</html>

