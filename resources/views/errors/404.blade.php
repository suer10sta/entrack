<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>404 · {{ __('messages.Not Found Page Title') }} · DalyTrac</title>
    <x-favicon />
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; min-height: 100svh; display: flex; flex-direction: column; background: #f8fafc; color: #0b2638; font-family: Figtree, ui-sans-serif, system-ui, sans-serif; }
        header { padding: 28px clamp(20px, 5vw, 64px); }
        .brand { color: #0b2638; font-size: 24px; font-weight: 750; letter-spacing: -1px; text-decoration: none; }
        .brand span { color: #2d7080; }
        main { flex: 1; display: grid; place-items: center; padding: 16px 20px 64px; }
        .card { width: 100%; max-width: 560px; padding: 48px 40px; text-align: center; border: 1px solid #e2e8f0; border-radius: 24px; background: #fff; box-shadow: 0 20px 60px -32px #0b263830; }
        .icon { display: grid; place-items: center; width: 64px; height: 64px; margin: 0 auto 24px; border-radius: 18px; background: #2d708012; color: #2d7080; }
        .code { margin: 0; color: #2d7080; font-size: clamp(64px, 14vw, 96px); line-height: 1; font-weight: 750; letter-spacing: -5px; }
        h1 { margin: 20px 0 12px; font-size: clamp(22px, 5vw, 28px); line-height: 1.25; letter-spacing: -.6px; }
        .description { max-width: 370px; margin: 0 auto; color: #64748b; font-size: 15px; line-height: 1.7; }
        .actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-top: 28px; }
        .button { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 11px 20px; border: 1px solid #2d7080; border-radius: 11px; background: #2d7080; color: #fff; font: inherit; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; }
        .button:hover { background: #245c69; }
        .secondary { background: #fff; border-color: #e2e8f0; color: #0b2638; }
        .secondary:hover { background: #f1f5f9; }
        .button:focus-visible, .brand:focus-visible { outline: 3px solid #e7b84b; outline-offset: 4px; }
        [hidden] { display: none !important; }
        @media(max-width: 480px) { header { padding: 24px 20px; } .card { padding: 32px 20px; border-radius: 18px; } .actions { flex-direction: column; } }
    </style>
</head>
<body>
    <header><a class="brand" href="{{ url('/') }}" aria-label="DalyTrac">Daly<span>Trac</span></a></header>
    <main>
        <section class="card" aria-labelledby="not-found-title">
            <div class="icon" aria-hidden="true">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Zm0 0v6h6M9 13l6 6m0-6-6 6"/></svg>
            </div>
            <p class="code" aria-hidden="true">404</p>
            <h1 id="not-found-title">{{ __('messages.Not Found Page Title') }}</h1>
            <p class="description">{{ __('messages.Not Found Page Description') }}</p>
            <div class="actions">
                <a class="button" href="{{ url('/') }}">{{ __('messages.Not Found Home') }}</a>
                <button class="button secondary" id="not-found-back" type="button" hidden>{{ __('messages.Not Found Back') }}</button>
            </div>
        </section>
    </main>
    <script>
        if (window.history.length > 1) {
            const back = document.getElementById('not-found-back');
            back.hidden = false;
            back.addEventListener('click', () => window.history.back());
        }
    </script>
</body>
</html>
