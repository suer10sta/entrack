<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __('messages.cgv_meta_title') }}</title>
    <meta name="description" content="{{ __('messages.cgv_meta_desc') }}">
    <meta name="robots" content="index,follow">
    <meta name="theme-color" content="#0b2638">
    <link rel="canonical" href="{{ route('cgv') }}">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='15' fill='%230b2638'/%3E%3Cpath d='M18 16h14c11 0 18 6 18 16s-7 16-18 16H18V16Zm12 23c6 0 10-2 10-7s-4-7-10-7h-2v14h2Z' fill='%238ccfd0'/%3E%3C/svg%3E">

    <meta property="og:title" content="{{ __('messages.cgv_meta_title') }}">
    <meta property="og:description" content="{{ __('messages.cgv_meta_desc') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('cgv') }}">

    <!-- Vite Assets (Tailwind CSS) -->
    @vite('resources/css/app.css')

    <style>
      [x-cloak] { display: none !important; }
      html, body { overflow-x: clip; }
      @supports not (overflow: clip) {
        html, body { overflow-x: hidden; }
      }
    </style>
  </head>

  <body class="bg-white font-normal text-[#1b3444] antialiased">

    <x-public.header />

    <main id="main">
      <div class="border-b border-[#e0e8e4] bg-[#f4f7f5] pb-[61px] pt-[69px] max-md:pb-[49px] max-md:pt-[52px]">
        <div class="mx-auto max-w-[1200px] px-6 max-md:px-4">
          <p class="mb-[14px] text-[0.72rem] font-bold uppercase tracking-[0.17em] text-brand-secondary">{{ __('messages.cgv_kicker') }}</p>
          <h1 class="text-[clamp(2.9rem,5vw,4rem)] font-semibold leading-[1.02] tracking-[-0.052em] text-brand-primary max-sm:text-[clamp(2.5rem,11vw,3.7rem)]">{{ __('messages.cgv_title') }}</h1>
          <p class="mt-[17px] max-w-[730px] text-[1.02rem] text-[#5b6b73]">{{ __('messages.cgv_lead') }}</p>
        </div>
      </div>

      <div class="mx-auto grid max-w-[1200px] items-start gap-[76px] px-6 pb-[110px] pt-[62px] max-md:gap-12 max-md:px-4 max-md:pb-[78px] max-md:pt-[42px] lg:grid-cols-[285px_minmax(0,1fr)]"
        x-data="{
          active: 'vendeur',
          init() {
            const observer = new IntersectionObserver((entries) => {
              entries.forEach(entry => { if (entry.isIntersecting) { this.active = entry.target.id; } });
            }, { rootMargin: '-35% 0px -55% 0px' });
            ['vendeur','clients','offre','prix','commande','paiement','livraison','duree','retractation','responsabilite','donnees','litiges'].forEach(id => { const el = document.getElementById(id); if (el) observer.observe(el); });
          }
        }">
        <aside class="lg:sticky lg:top-[112px]" aria-label="{{ __('messages.cgv_nav_label') }}">
          <p class="mb-[17px] text-[0.7rem] font-bold uppercase tracking-[0.12em] text-[#6e8584]">{{ __('messages.cgv_nav_heading') }}</p>
          <nav aria-label="{{ __('messages.cgv_nav_label') }}" class="grid border-t border-[#dce6e4] max-sm:grid-cols-1 sm:grid-cols-2 sm:gap-x-[19px] lg:grid-cols-1">
            <a href="#vendeur" @click="active = 'vendeur'" :class="active === 'vendeur' ? 'bg-[#eef5f2] font-bold text-brand-secondary' : 'font-medium text-brand-primary'" class="flex min-h-[52px] items-center rounded-r-md border-b border-[#dce6e4] px-3 text-[0.85rem] no-underline transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.cgv_nav_vendeur') }}</a>
            <a href="#clients" @click="active = 'clients'" :class="active === 'clients' ? 'bg-[#eef5f2] font-bold text-brand-secondary' : 'font-medium text-brand-primary'" class="flex min-h-[52px] items-center rounded-r-md border-b border-[#dce6e4] px-3 text-[0.85rem] no-underline transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.cgv_nav_clients') }}</a>
            <a href="#offre" @click="active = 'offre'" :class="active === 'offre' ? 'bg-[#eef5f2] font-bold text-brand-secondary' : 'font-medium text-brand-primary'" class="flex min-h-[52px] items-center rounded-r-md border-b border-[#dce6e4] px-3 text-[0.85rem] no-underline transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.cgv_nav_offre') }}</a>
            <a href="#prix" @click="active = 'prix'" :class="active === 'prix' ? 'bg-[#eef5f2] font-bold text-brand-secondary' : 'font-medium text-brand-primary'" class="flex min-h-[52px] items-center rounded-r-md border-b border-[#dce6e4] px-3 text-[0.85rem] no-underline transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.cgv_nav_prix') }}</a>
            <a href="#commande" @click="active = 'commande'" :class="active === 'commande' ? 'bg-[#eef5f2] font-bold text-brand-secondary' : 'font-medium text-brand-primary'" class="flex min-h-[52px] items-center rounded-r-md border-b border-[#dce6e4] px-3 text-[0.85rem] no-underline transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.cgv_nav_commande') }}</a>
            <a href="#paiement" @click="active = 'paiement'" :class="active === 'paiement' ? 'bg-[#eef5f2] font-bold text-brand-secondary' : 'font-medium text-brand-primary'" class="flex min-h-[52px] items-center rounded-r-md border-b border-[#dce6e4] px-3 text-[0.85rem] no-underline transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.cgv_nav_paiement') }}</a>
            <a href="#livraison" @click="active = 'livraison'" :class="active === 'livraison' ? 'bg-[#eef5f2] font-bold text-brand-secondary' : 'font-medium text-brand-primary'" class="flex min-h-[52px] items-center rounded-r-md border-b border-[#dce6e4] px-3 text-[0.85rem] no-underline transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.cgv_nav_livraison') }}</a>
            <a href="#duree" @click="active = 'duree'" :class="active === 'duree' ? 'bg-[#eef5f2] font-bold text-brand-secondary' : 'font-medium text-brand-primary'" class="flex min-h-[52px] items-center rounded-r-md border-b border-[#dce6e4] px-3 text-[0.85rem] no-underline transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.cgv_nav_duree') }}</a>
            <a href="#retractation" @click="active = 'retractation'" :class="active === 'retractation' ? 'bg-[#eef5f2] font-bold text-brand-secondary' : 'font-medium text-brand-primary'" class="flex min-h-[52px] items-center rounded-r-md border-b border-[#dce6e4] px-3 text-[0.85rem] no-underline transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.cgv_nav_retractation') }}</a>
            <a href="#responsabilite" @click="active = 'responsabilite'" :class="active === 'responsabilite' ? 'bg-[#eef5f2] font-bold text-brand-secondary' : 'font-medium text-brand-primary'" class="flex min-h-[52px] items-center rounded-r-md border-b border-[#dce6e4] px-3 text-[0.85rem] no-underline transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.cgv_nav_responsabilite') }}</a>
            <a href="#donnees" @click="active = 'donnees'" :class="active === 'donnees' ? 'bg-[#eef5f2] font-bold text-brand-secondary' : 'font-medium text-brand-primary'" class="flex min-h-[52px] items-center rounded-r-md border-b border-[#dce6e4] px-3 text-[0.85rem] no-underline transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.cgv_nav_donnees') }}</a>
            <a href="#litiges" @click="active = 'litiges'" :class="active === 'litiges' ? 'bg-[#eef5f2] font-bold text-brand-secondary' : 'font-medium text-brand-primary'" class="flex min-h-[52px] items-center rounded-r-md border-b border-[#dce6e4] px-3 text-[0.85rem] no-underline transition-colors duration-300 hover:text-brand-secondary">{{ __('messages.cgv_nav_litiges') }}</a>
          </nav>
        </aside>

        <div class="min-w-0">
          <section id="vendeur" aria-labelledby="vendeur-title" class="scroll-mt-[110px]">
            <h2 id="vendeur-title" class="text-[clamp(1.65rem,2.4vw,1.6rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgv_s1_title') }}</h2>
            <div class="mt-[18px] rounded-lg border border-[#dce6e4] bg-[#f8faf9] p-5 text-[0.93rem] leading-[1.75] text-[#50666c]">
              <p class="m-0"><strong class="font-bold text-brand-primary">{{ __('messages.cgv_s1_seller') }}</strong> <span class="rounded px-1.5 py-0.5 text-[1rem] font-medium ">BME SRL</span></p>
              <p class="mt-2"><strong class="font-bold text-brand-primary">{{ __('messages.cgv_s1_hq') }}</strong> <span class="rounded px-1.5 py-0.5 text-[1rem] font-medium ">Rue Colonel Bourg 127-129, 1140 Bruxelles (Belgique)</span></p>
              <p class="mt-2"><strong class="font-bold text-brand-primary">{{ __('messages.cgv_s1_bce') }}</strong> <span class="rounded px-1.5 py-0.5 text-[1rem] font-medium ">0823.758.741 </span> <strong class="font-bold text-brand-primary">{{ __('messages.cgv_s1_tva') }}</strong> <span class="rounded px-1.5 py-0.5 text-[1rem] font-medium"> BE 0823.758.741</span></p>
              <p class="mt-2"><strong class="font-bold text-brand-primary">{{ __('messages.cgv_s1_email') }}</strong> <a href="mailto:support@dalytrac.com" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">support@dalytrac.com</a> <strong class="font-bold text-brand-primary">{{ __('messages.cgv_s1_tel') }}</strong><span class="rounded px-1.5 py-0.5 text-[1rem] font-medium ">+32 2 555 00 00</span></p>
            </div>
          </section>

          <section id="clients" aria-labelledby="clients-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="clients-title" class="text-[clamp(1.65rem,2.4vw,1.6rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgv_s2_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s2_p1') }}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s2_p2') }}</p>
          </section>

          <section id="offre" aria-labelledby="offre-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="offre-title" class="text-[clamp(1.65rem,2.4vw,1.6rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgv_s3_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s3_p1') }}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s3_p2') }}</p>
          </section>

          <section id="prix" aria-labelledby="prix-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="prix-title" class="text-[clamp(1.65rem,2.4vw,1.6rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgv_s4_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s4_p1') }}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{!! __('messages.cgv_s4_p2', ['tarifs' => '<a href="' . route('tarifs') . '" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">' . __('messages.cgv_tarifs_link') . '</a>']) !!}</p>
          </section>

          <section id="commande" aria-labelledby="commande-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="commande-title" class="text-[clamp(1.65rem,2.4vw,1.6rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgv_s5_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s5_p1') }}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s5_p2') }}</p>
          </section>

          <section id="paiement" aria-labelledby="paiement-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="paiement-title" class="text-[clamp(1.65rem,2.4vw,1.6rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgv_s6_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s6_p1') }}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s6_p2') }}</p>
          </section>

          <section id="livraison" aria-labelledby="livraison-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="livraison-title" class="text-[clamp(1.65rem,2.4vw,1.6rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgv_s7_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s7_p1') }}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s7_p2') }}</p>
          </section>

          <section id="duree" aria-labelledby="duree-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="duree-title" class="text-[clamp(1.65rem,2.4vw,1.6rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgv_s8_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s8_p1') }}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s8_p2') }}</p>
          </section>

          <section id="retractation" aria-labelledby="retractation-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="retractation-title" class="text-[clamp(1.65rem,2.4vw,1.6rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgv_s9_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s9_p1') }}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s9_p2') }}</p>
          </section>

          <section id="responsabilite" aria-labelledby="responsabilite-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="responsabilite-title" class="text-[clamp(1.65rem,2.4vw,1.6rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgv_s10_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s10_p1') }}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s10_p2') }}</p>
          </section>

          <section id="donnees" aria-labelledby="donnees-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="donnees-title" class="text-[clamp(1.65rem,2.4vw,1.6rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgv_s11_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{!! __('messages.cgv_s11_p1', ['privacy' => '<a href="' . route('confidentialite') . '" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">' . __('messages.cgv_privacy_link') . '</a>']) !!}</p>
          </section>

          <section id="litiges" aria-labelledby="litiges-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="litiges-title" class="text-[clamp(1.65rem,2.4vw,1.6rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgv_s12_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{!! __('messages.cgv_s12_p1', ['email' => '<a href="mailto:support@dalytrac.com" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">support@dalytrac.com</a>']) !!}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgv_s12_p2') }}</p>
            <p class="mt-3 text-[0.8rem] text-[#77908d]">{{ __('messages.cgv_updated') }}</p>
          </section>
        </div>
      </div>
    </main>

    <x-public.footer />

  </body>

</html>
