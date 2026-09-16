<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/dalytraclogo.png') }}" />
    <link rel="icon" type="image/png" href="{{ asset('img/dalytraclogo.png') }}" />
    <title>DalyTrac - Vérifiez votre email</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gradient-to-b from-sky-50 via-sky-100 to-slate-50 text-foreground font-sans antialiased">

    <main class="min-h-screen relative flex items-center justify-center px-4 py-10 overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-32 -left-32 h-72 w-72 rounded-full bg-sky-300/20 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-sky-400/20 blur-3xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-lg">
            <div class="rounded-2xl border-4 border-sky-200/60 bg-white/95 shadow-2xl ring-1 ring-border/40 backdrop-blur-md px-8 py-10 sm:px-10 sm:py-12">

                <div class="flex items-center justify-center mb-6">
                    <img src="{{ asset('img/dalytraclogo.png') }}" alt="DalyTrac logo" class="h-14 w-auto object-contain" />
                </div>

                <div class="text-center mb-8">
                    <div class="mb-4 inline-flex items-center justify-center rounded-xl bg-primary/10 p-1 pr-3 text-xs font-medium text-primary">
                        <span class="mr-2 flex h-6 w-6 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                            <i class="fa-solid fa-envelope-circle-check text-[11px]"></i>
                        </span>
                        Vérification requise
                    </div>
                    <h1 class="text-2xl font-semibold tracking-tight text-foreground sm:text-3xl">Vérifiez votre email</h1>
                    <p class="mt-2 text-sm text-muted-foreground leading-relaxed">
                        Merci pour votre inscription ! Un email de confirmation vient de vous être envoyé.<br>
                        Veuillez cliquer sur le lien dans l'email pour activer votre compte.
                    </p>
                </div>

                @if(session('status'))
                <div class="mb-6 rounded-lg border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600 flex items-center gap-2" role="alert">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('status') }}</span>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 rounded-lg border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-600 flex items-center gap-2" role="alert">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                <div class="mb-6 rounded-xl border border-sky-200/60 bg-sky-50/80 px-5 py-4">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-600">
                            <i class="fa-solid fa-inbox text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-700">Vous n'avez pas reçu l'email ?</p>
                            <p class="mt-1 text-xs text-slate-500">Vérifiez votre dossier spam ou demandez un nouvel email de vérification ci-dessous.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit"
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm shadow-primary/40 transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/70 focus-visible:ring-offset-2">
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            <span>Renvoyer l'email de vérification</span>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex w-full items-center justify-center gap-2 rounded-lg border border-border bg-background px-4 py-2.5 text-sm font-medium text-muted-foreground transition hover:bg-accent hover:text-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/40">
                            <i class="fa-solid fa-right-from-bracket text-xs"></i>
                            <span>Se déconnecter</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </main>

</body>

</html>
