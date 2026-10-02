<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __('messages.mentions_meta_title') }}</title>
    <meta name="description" content="{{ __('messages.mentions_meta_desc') }}">
    <meta name="robots" content="index,follow">
    <meta name="theme-color" content="#0b2638">
    <link rel="canonical" href="{{ route('mentions-legales') }}">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='15' fill='%230b2638'/%3E%3Cpath d='M18 16h14c11 0 18 6 18 16s-7 16-18 16H18V16Zm12 23c6 0 10-2 10-7s-4-7-10-7h-2v14h2Z' fill='%238ccfd0'/%3E%3C/svg%3E">

    <meta property="og:title" content="{{ __('messages.mentions_og_title') }}">
    <meta property="og:description" content="{{ __('messages.mentions_og_desc') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('mentions-legales') }}">

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

    @php
      $mentionsEmailLink = '<a href="mailto:support@dalytrac.com" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">support@dalytrac.com</a>';
      $mentionsPrivacyLink = '<a href="' . route('confidentialite') . '" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">' . __('messages.mentions_s6_privacy') . '</a>';
      $mentionsCookiesLink = '<a href="' . route('cookies') . '" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">' . __('messages.mentions_s6_cookies') . '</a>';
    @endphp

    <main id="main">
      <div class="border-b border-[#e0e8e4] bg-[#f4f7f5]  pb-[61px] pt-[69px] max-md:pb-[49px] max-md:pt-[52px]">
        <div class="mx-auto max-w-[1200px] px-6 max-md:px-4">
          <p class="mb-[14px] text-[0.72rem] font-bold uppercase tracking-[0.17em] text-brand-secondary">{{ __('messages.mentions_eyebrow') }}</p>
          <h1 class="text-[clamp(2.9rem,5vw,4.8rem)] font-semibold leading-[1.02] tracking-[-0.052em] text-brand-primary max-sm:text-[clamp(2.5rem,11vw,3.7rem)]">{{ __('messages.mentions_title') }}</h1>
          <p class="mt-[17px] max-w-[730px] text-[1.02rem] text-[#5b6b73]">{{ __('messages.mentions_intro') }}</p>
        </div>
      </div>

      <div class="mx-auto grid max-w-[1200px] items-start gap-[76px] px-6 pb-[110px] pt-[62px] max-md:gap-12 max-md:px-4 max-md:pb-[78px] max-md:pt-[42px] lg:grid-cols-[285px_minmax(0,1fr)]">
        <aside class="lg:sticky lg:top-[130px]" aria-label="{{ __('messages.mentions_nav_label') }}">
          <p class="mb-[17px] text-[0.7rem] font-bold uppercase tracking-[0.12em] text-[#6e8584]">{{ __('messages.mentions_nav_heading') }}</p>
          <nav aria-label="{{ __('messages.mentions_nav_sections') }}" class="grid border-t border-[#dce6e4] max-sm:grid-cols-1 sm:grid-cols-2 sm:gap-x-[19px] lg:grid-cols-1">
            <a href="#editeur" data-target="editeur" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.mentions_nav_editor') }}</span>
            </a>
            <a href="#hebergement" data-target="hebergement" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.mentions_nav_hosting') }}</span>
            </a>
            <a href="#contact" data-target="contact" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.mentions_nav_contact') }}</span>
            </a>
            <a href="#propriete" data-target="propriete" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.mentions_nav_ip') }}</span>
            </a>
            <a href="#responsabilite" data-target="responsabilite" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.mentions_nav_liability') }}</span>
            </a>
            <a href="#donnees" data-target="donnees" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.mentions_nav_data') }}</span>
            </a>
            <a href="#droit" data-target="droit" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.mentions_nav_law') }}</span>
            </a>
          </nav>
        </aside>

        <div class="min-w-0">
          <section id="editeur" aria-labelledby="editeur-title" class="scroll-mt-[110px]">
            <h2 id="editeur-title" class="text-[clamp(1.65rem,2.4vw,2rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.mentions_s1_title') }}</h2>
            <dl class="mt-[18px] overflow-hidden rounded-lg border border-[#dce6e4]">
              <div class="grid border-b border-[#eef3f1] bg-[#f8faf9] sm:grid-cols-[220px_1fr] max-sm:grid-cols-1">
                <dt class="px-5 py-3.5 text-[0.85rem] font-bold text-brand-primary">{{ __('messages.mentions_s1_company') }}</dt>
                <dd class="m-0 px-5 py-3.5 text-[0.85rem] text-[#1b3444] max-sm:pt-0"><span class="rounded  px-1.5 py-0.5 text-[0.8rem] font-medium ">BME SRL</span></dd>
              </div>
              <div class="grid border-b border-[#eef3f1] sm:grid-cols-[220px_1fr] max-sm:grid-cols-1">
                <dt class="px-5 py-3.5 text-[0.85rem] font-bold text-brand-primary">{{ __('messages.mentions_s1_form') }}</dt>
                <dd class="m-0 px-5 py-3.5 text-[0.85rem] text-[#1b3444] max-sm:pt-0"><span class="rounded  px-1.5 py-0.5 text-[0.8rem] font-medium ">SRL</span></dd>
              </div>
              <div class="grid border-b border-[#eef3f1] bg-[#f8faf9] sm:grid-cols-[220px_1fr] max-sm:grid-cols-1">
                <dt class="px-5 py-3.5 text-[0.85rem] font-bold text-brand-primary">{{ __('messages.mentions_s1_address') }}</dt>
                <dd class="m-0 px-5 py-3.5 text-[0.85rem] text-[#1b3444] max-sm:pt-0"><span class="rounded  px-1.5 py-0.5 text-[0.8rem] font-medium ">Rue Colonel Bourg 127-129, 1140 Bruxelles (Belgique)</span></dd>
              </div>
              <div class="grid border-b border-[#eef3f1] sm:grid-cols-[220px_1fr] max-sm:grid-cols-1">
                <dt class="px-5 py-3.5 text-[0.85rem] font-bold text-brand-primary">{{ __('messages.mentions_s1_bce') }}</dt>
                <dd class="m-0 px-5 py-3.5 text-[0.85rem] text-[#1b3444] max-sm:pt-0"><span class="rounded  px-1.5 py-0.5 text-[0.8rem] font-medium ">0823.758.741</span></dd>
              </div>
              <div class="grid border-b border-[#eef3f1] bg-[#f8faf9] sm:grid-cols-[220px_1fr] max-sm:grid-cols-1">
                <dt class="px-5 py-3.5 text-[0.85rem] font-bold text-brand-primary">{{ __('messages.mentions_s1_vat') }}</dt>
                <dd class="m-0 px-5 py-3.5 text-[0.85rem] text-[#1b3444] max-sm:pt-0"><span class="rounded  px-1.5 py-0.5 text-[0.8rem] font-medium ">BE 0823.758.741</span></dd>
              </div>
              <div class="grid border-b border-[#eef3f1] sm:grid-cols-[220px_1fr] max-sm:grid-cols-1">
                <dt class="px-5 py-3.5 text-[0.85rem] font-bold text-brand-primary">{{ __('messages.mentions_s1_phone') }}</dt>
                <dd class="m-0 px-5 py-3.5 text-[0.85rem] text-[#1b3444] max-sm:pt-0"><span class="rounded  px-1.5 py-0.5 text-[0.8rem] font-medium ">+32 2 609 04 34</span></dd>
              </div>
              <div class="grid bg-[#f8faf9] sm:grid-cols-[220px_1fr] max-sm:grid-cols-1">
                <dt class="px-5 py-3.5 text-[0.85rem] font-bold text-brand-primary">{{ __('messages.mentions_s1_email') }}</dt>
                <dd class="m-0 px-5 py-3.5 text-[0.85rem] text-[#1b3444] max-sm:pt-0"><a href="mailto:support@dalytrac.com" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">support@dalytrac.com</a></dd>
              </div>
            </dl>
          </section>

          <section id="hebergement" aria-labelledby="hebergement-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="hebergement-title" class="text-[clamp(1.65rem,2.4vw,2rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.mentions_s2_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{!! __('messages.mentions_s2_text', ['email' => $mentionsEmailLink]) !!}</p>
          </section>

          <section id="contact" aria-labelledby="contact-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="contact-title" class="text-[clamp(1.65rem,2.4vw,2rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.mentions_s3_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{!! __('messages.mentions_s3_p1', ['email' => $mentionsEmailLink]) !!}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{!! __('messages.mentions_s3_p2', ['email' => $mentionsEmailLink]) !!}</p>
          </section>

          <section id="propriete" aria-labelledby="propriete-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="propriete-title" class="text-[clamp(1.65rem,2.4vw,2rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.mentions_s4_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.mentions_s4_p1') }}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.mentions_s4_p2') }}</p>
          </section>

          <section id="responsabilite" aria-labelledby="responsabilite-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="responsabilite-title" class="text-[clamp(1.65rem,2.4vw,2rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.mentions_s5_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.mentions_s5_text') }}</p>
          </section>

          <section id="donnees" aria-labelledby="donnees-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="donnees-title" class="text-[clamp(1.65rem,2.4vw,2rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.mentions_s6_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{!! __('messages.mentions_s6_p1', ['privacy' => $mentionsPrivacyLink]) !!}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{!! __('messages.mentions_s6_p2', ['cookies' => $mentionsCookiesLink]) !!}</p>
            <p class="mt-5"><a href="{{ route('cookies') }}" class="inline-block rounded-md border border-[#d5dfdd] bg-[#f4f7f5] px-4 py-2 text-[0.8rem] font-bold text-brand-secondary no-underline transition-all duration-300 hover:-translate-y-0.5 hover:border-brand-secondary">{{ __('messages.mentions_s6_manage') }}</a></p>
          </section>

          <section id="droit" aria-labelledby="droit-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="droit-title" class="text-[clamp(1.65rem,2.4vw,2rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.mentions_s7_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.mentions_s7_text') }}</p>
          </section>
        </div>
      </div>
    </main>

    <x-public.footer />

    <script>
      (() => {
        const links = document.querySelectorAll('.docs-nav-link');
        const sections = [...document.querySelectorAll('main section[id]')];

        const setActiveLink = (targetId) => {
          links.forEach((link) => {
            const isActive = link.dataset.target === targetId;
            const indicator = link.querySelector('span');

            link.classList.toggle('text-brand-secondary', isActive);
            link.classList.toggle('text-brand-primary', !isActive);
            link.classList.toggle('font-semibold', isActive);
            link.setAttribute('aria-current', isActive ? 'true' : 'false');

            if (indicator) {
              indicator.style.height = isActive ? '58%' : '0';
              indicator.style.opacity = isActive ? '1' : '0';
            }
          });
        };

        const updateFromVisibleSection = () => {
          if (!sections.length || !links.length) {
            return;
          }

          let currentId = sections[0].id;

          for (const section of sections) {
            const rect = section.getBoundingClientRect();
            if (rect.top <= window.innerHeight * 0.35 && rect.bottom >= window.innerHeight * 0.35) {
              currentId = section.id;
              break;
            }
            if (rect.top > window.innerHeight * 0.35) {
              break;
            }
          }

          setActiveLink(currentId);
        };

        if (sections.length && links.length) {
          if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
              const visibleEntries = entries
                .filter((entry) => entry.isIntersecting)
                .sort((a, b) => b.intersectionRatio - a.intersectionRatio);

              if (visibleEntries.length > 0) {
                setActiveLink(visibleEntries[0].target.id);
              }
            }, {
              root: null,
              threshold: [0.2, 0.35, 0.5, 0.7],
              rootMargin: '-12% 0px -40% 0px'
            });

            sections.forEach((section) => observer.observe(section));
          } else {
            window.addEventListener('scroll', updateFromVisibleSection, { passive: true });
            window.addEventListener('load', updateFromVisibleSection);
          }

          updateFromVisibleSection();
        }
      })();
    </script>

  </body>

</html>
