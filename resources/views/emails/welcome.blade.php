@php
    $appName = 'DalyTrac';
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bienvenue | {{ $appName }}</title>
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

        .highlight-box {
            border-radius: 8px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1px solid #bfdbfe;
            padding: 16px 18px;
            margin: 20px 0;
        }

        .highlight-box p {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: #1e40af;
        }

        .highlight-box p:last-child {
            margin-bottom: 0;
        }

        .button-wrapper {
            text-align: center;
            margin: 28px 0 20px 0;
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
            <p class="greeting">Bienvenue {{ $userName }} !</p>

            <p class="body-text">
                Votre compte a bien été créé pour l'entreprise <strong>{{ $entrepriseName }}</strong>.
                Nous sommes ravis de vous accueillir sur {{ $appName }}.
            </p>

            <div class="highlight-box">
                <p>🎁 Votre essai gratuit de <strong>{{ $trialDays }} jours</strong> est maintenant actif.</p>
                <p>Profitez de toutes les fonctionnalités sans engagement.</p>
            </div>

            <p class="body-text">
                Voici ce que vous pouvez faire dès maintenant :
            </p>
            <ul style="font-size:14px; line-height:1.8; color:#4b5563; padding-left:20px;">
                <li>Gérer vos employés et leurs informations</li>
                <li>Suivre les jours de travail et les congés</li>
                <li>Générer des factures et devis</li>
                <li>Planifier vos événements calendrier</li>
            </ul>

            <div class="button-wrapper">
                <a href="{{ url('/dashboard') }}" class="primary-button" target="_blank" rel="noopener">
                    Accéder à mon tableau de bord
                </a>
            </div>
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
