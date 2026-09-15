@php
    $appName = 'DalyTrac';
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Essai bientôt expiré | {{ $appName }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            background-color: #f5f7fb;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1f2933;
        }

        a { color: #2563eb; text-decoration: none; }

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

        .email-logo img {
            max-width: 140px;
            height: auto;
            display: block;
            margin: 0 auto;
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

        .warning-box {
            border-radius: 8px;
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            border: 1px solid #f59e0b;
            padding: 16px 18px;
            margin: 20px 0;
        }

        .warning-box p {
            margin: 0;
            font-size: 14px;
            color: #92400e;
        }

        .button-wrapper {
            text-align: center;
            margin: 28px 0 20px 0;
        }

        .primary-button {
            display: inline-block;
            padding: 12px 28px;
            border-radius: 999px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff !important;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-decoration: none;
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
            .email-container { border-radius: 0; }
            .email-body, .email-footer { padding: 20px 16px; }
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
            <div class="email-header-title">{{ $appName }}</div>
        </div>

        <div class="email-body">
            <p class="greeting">Bonjour {{ $userName }},</p>

            <p class="body-text">
                Votre essai gratuit pour l'entreprise <strong>{{ $entrepriseName }}</strong> arrive à sa fin.
            </p>

            <div class="warning-box">
                <p>⏰ Il vous reste <strong>{{ $daysRemaining }} jour{{ $daysRemaining > 1 ? 's' : '' }}</strong> avant l'expiration de votre essai le <strong>{{ $trialEndsAt }}</strong>.</p>
            </div>

            <p class="body-text">
                Pour continuer à profiter de toutes les fonctionnalités de {{ $appName }}, passez à un plan payant.
                Vous pourrez :
            </p>
            <ul style="font-size:14px; line-height:1.8; color:#4b5563; padding-left:20px;">
                <li>Accéder à toutes les fonctionnalités sans interruption</li>
                <li>Ajouter plus d'utilisateurs à votre équipe</li>
                <li>Bénéficiers du support prioritaire</li>
            </ul>

            <div class="button-wrapper">
                <a href="{{ $upgradeUrl }}" class="primary-button" target="_blank" rel="noopener">
                    Choisir un plan
                </a>
            </div>

            <p class="body-text" style="font-size:13px; color:#6b7280;">
                Si vous ne souscrivez pas, votre compte sera limité après l'expiration de l'essai.
            </p>
        </div>

        <div class="email-footer">
            <p class="footer-text">
                Cordialement,<br>L'équipe {{ $appName }}
            </p>
            <p class="small-muted">
                © {{ date('Y') }} {{ $appName }}. Tous droits réservés.
            </p>
        </div>
    </div>
</div>
</body>
</html>
