@php
    $appName = 'DalyTrac';
    $logoUrl = config('app.url', 'https://dalytrac.com') . '/img/dalytraclogo.png';
@endphp

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérifiez votre email | {{ $appName }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            background-color: #f0f7ff;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1f2933;
        }

        a { color: #2563eb; text-decoration: none; }

        .wrapper {
            width: 100%;
            background-color: #f0f7ff;
            padding: 40px 0;
        }

        .email-container {
            max-width: 640px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(15, 23, 42, 0.10);
            overflow: hidden;
        }

        .email-header {
            text-align: center;
            padding: 32px 28px 20px 28px;
            background: #ffffff;
            border-bottom: 3px solid #2563eb;
        }

        .email-logo img {
            max-width: 180px;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .email-body {
            padding: 36px 32px;
        }

        .badge {
            display: block;
            width: fit-content;
            margin: 0 auto 20px auto;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            padding: 6px 16px;
            font-size: 12px;
            font-weight: 600;
            color: #2563eb;
            text-align: center;
        }

        .title {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 12px 0;
        }

        .subtitle {
            text-align: center;
            font-size: 14px;
            line-height: 1.7;
            color: #64748b;
            margin: 0 0 28px 0;
        }

        .highlight-box {
            border-radius: 10px;
            background: linear-gradient(135deg, #eff6ff, #f0f9ff);
            border: 1px solid #bfdbfe;
            padding: 18px 20px;
            margin: 0 0 28px 0;
        }

        .highlight-box p {
            margin: 0;
            font-size: 13px;
            line-height: 1.6;
            color: #475569;
        }

        .button-wrapper {
            text-align: center;
            margin: 0 0 28px 0;
        }

        .primary-button {
            display: inline-block;
            padding: 14px 36px;
            border-radius: 999px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.02em;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        }

        .divider {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 0 0 24px 0;
        }

        .footer-text {
            text-align: center;
            font-size: 12px;
            line-height: 1.6;
            color: #94a3b8;
            margin: 0;
        }

        .footer-text a {
            color: #64748b;
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .email-container { border-radius: 0; }
            .email-body { padding: 28px 20px; }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="email-container">
        <div class="email-header">
            <div class="email-logo">
                <img src="{{ $logoUrl }}" alt="{{ $appName }}">
            </div>
        </div>

        <div class="email-body">
            <div class="badge">VERIFICATION REQUISE</div>

            <h1 class="title">Vérifiez votre adresse email</h1>

            <p class="subtitle">
                Bonjour <strong>{{ $userName }}</strong>,<br>
                Merci pour votre inscription sur {{ $appName }}.<br>
                Cliquez sur le bouton ci-dessous pour activer votre compte.
            </p>

            <div class="button-wrapper">
                <a href="{{ $verificationUrl }}" class="primary-button" target="_blank" rel="noopener">
                    Vérifier mon email
                </a>
            </div>

            <div class="highlight-box">
                <p>
                    Ce lien expire dans <strong>60 minutes</strong>.
                    Si vous n'avez pas créé de compte, ignorez cet email.
                </p>
            </div>

            <hr class="divider">

            <p class="footer-text">
                © {{ date('Y') }} {{ $appName }}. Tous droits réservés.<br>
                Si le bouton ne fonctionne pas, copiez ce lien :<br>
                <a href="{{ $verificationUrl }}" style="word-break:break-all;">{{ $verificationUrl }}</a>
            </p>
        </div>
    </div>
</div>
</body>
</html>
