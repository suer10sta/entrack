<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

  <head>
    <x-favicon />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __('messages.cgu_meta_title') }}</title>
    <meta name="description" content="{{ __('messages.cgu_meta_desc') }}">
    <meta name="robots" content="index,follow">
    <meta name="theme-color" content="#0b2638">
    <link rel="canonical" href="{{ route('cgu') }}">


    <meta property="og:title" content="{{ __('messages.cgu_meta_title') }}">
    <meta property="og:description" content="{{ __('messages.cgu_meta_desc') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('cgu') }}">

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
          <p class="mb-[14px] text-[0.72rem] font-bold uppercase tracking-[0.17em] text-brand-secondary">{{ __('messages.cgu_kicker') }}</p>
          <h1 class="text-[clamp(2.9rem,5vw,4rem)] font-semibold leading-[1.02] tracking-[-0.052em] text-brand-primary max-sm:text-[clamp(2.5rem,11vw,3.7rem)]">{{ __('messages.cgu_title') }}</h1>
          <p class="mt-[17px] max-w-[730px] text-[1.02rem] text-[#5b6b73]">{{ __('messages.cgu_lead') }}</p>
        </div>
      </div>

      <div class="mx-auto grid max-w-[1200px] items-start gap-[76px] px-6 pb-[110px] pt-[62px] max-md:gap-12 max-md:px-4 max-md:pb-[78px] max-md:pt-[42px] lg:grid-cols-[285px_minmax(0,1fr)]">
        <aside class="lg:sticky lg:top-[112px]" aria-label="{{ __('messages.cgu_nav_label') }}">
          <p class="mb-[17px] text-[0.7rem] font-bold uppercase tracking-[0.12em] text-[#6e8584]">{{ __('messages.cgu_nav_heading') }}</p>
          <nav aria-label="{{ __('messages.cgu_nav_label') }}" class="grid border-t border-[#dce6e4] max-sm:grid-cols-1 sm:grid-cols-2 sm:gap-x-[19px] lg:grid-cols-1">
            <a href="#objet" data-target="objet" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cgu_nav_objet') }}</span>
            </a>
            <a href="#editeur" data-target="editeur" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cgu_nav_editeur') }}</span>
            </a>
            <a href="#acceptation" data-target="acceptation" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cgu_nav_acceptation') }}</span>
            </a>
            <a href="#acces" data-target="acces" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cgu_nav_acces') }}</span>
            </a>
            <a href="#compte" data-target="compte" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cgu_nav_compte') }}</span>
            </a>
            <a href="#essai" data-target="essai" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cgu_nav_essai') }}</span>
            </a>
            <a href="#licence" data-target="licence" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cgu_nav_licence') }}</span>
            </a>
            <a href="#disponibilite" data-target="disponibilite" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cgu_nav_disponibilite') }}</span>
            </a>
            <a href="#propriete" data-target="propriete" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cgu_nav_propriete') }}</span>
            </a>
            <a href="#suspension" data-target="suspension" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cgu_nav_suspension') }}</span>
            </a>
            <a href="#responsabilite" data-target="responsabilite" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cgu_nav_responsabilite') }}</span>
            </a>
            <a href="#finales" data-target="finales" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cgu_nav_finales') }}</span>
            </a>
          </nav>
        </aside>

        <div class="min-w-0">
          <section id="objet" aria-labelledby="objet-title" class="scroll-mt-[110px]">
            <h2 id="objet-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgu_s1_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgu_s1_p1') }}</p>
          </section>

          <section id="editeur" aria-labelledby="editeur-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="editeur-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgu_s2_title') }}</h2>
            <div class="mt-[18px] rounded-lg border border-[#dce6e4] bg-[#f8faf9] p-5 text-[0.93rem] leading-[1.75] text-[#50666c]">
              <p class="m-0">{{ __('messages.cgu_s2_p1') }} {{ __('messages.cgu_s2_p2') }} <a href="mailto:support@dalytrac.com" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">support@dalytrac.com</a>.</p>
            </div>
          </section>

          <section id="acceptation" aria-labelledby="acceptation-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="acceptation-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgu_s3_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgu_s3_p1') }}</p>
          </section>

          <section id="acces" aria-labelledby="acces-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="acces-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgu_s4_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgu_s4_p1') }}</p>
          </section>

          <section id="compte" aria-labelledby="compte-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="compte-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgu_s5_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgu_s5_p1') }}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgu_s5_p2') }} <a href="mailto:support@dalytrac.com" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">support@dalytrac.com</a>.</p>
          </section>

          <section id="essai" aria-labelledby="essai-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="essai-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgu_s6_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgu_s6_p1') }}</p>
          </section>

          <section id="licence" aria-labelledby="licence-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="licence-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgu_s7_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgu_s7_p1') }}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgu_s7_p2') }}</p>
          </section>

          <section id="disponibilite" aria-labelledby="disponibilite-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="disponibilite-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgu_s8_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgu_s8_p1') }}</p>
          </section>

          <section id="propriete" aria-labelledby="propriete-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="propriete-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgu_s9_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgu_s9_p1') }}</p>
          </section>

          <section id="suspension" aria-labelledby="suspension-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="suspension-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgu_s10_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgu_s10_p1') }}</p>
          </section>

          <section id="responsabilite" aria-labelledby="responsabilite-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="responsabilite-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgu_s11_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgu_s11_p1') }}</p>
          </section>

          <section id="finales" aria-labelledby="finales-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="finales-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cgu_s12_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgu_s12_p1') }} <a href="{{ route('confidentialite') }}" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">{{ __('messages.cgu_privacy_link') }}</a> {{ __('messages.cgu_s12_p2') }} <a href="{{ route('cookies') }}" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">{{ __('messages.cgu_cookies_link') }}</a>.</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cgu_s12_p3') }}</p>
            <p class="mt-3 text-[0.8rem] text-[#77908d]">{{ __('messages.cgu_updated') }}</p>
          </section>
        </div>
      </div>
    </main>

    <x-public.footer />

    <script>
      (() => {
        const init = () => {
          const links = document.querySelectorAll('.docs-nav-link');
          const sections = [...document.querySelectorAll('main section[id]')];

          let manualOverride = null;

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

          const getReferencePoint = () => window.innerHeight * 0.22;

          const updateFromVisibleSection = () => {
            if (!sections.length || !links.length) {
              return;
            }

            if (manualOverride) {
              setActiveLink(manualOverride);
              return;
            }

            let bestSection = sections[0];
            let bestDistance = Number.POSITIVE_INFINITY;
            const referencePoint = getReferencePoint();

            sections.forEach((section) => {
              const rect = section.getBoundingClientRect();
              const isVisible = rect.bottom > 0 && rect.top < window.innerHeight;

              if (!isVisible) {
                return;
              }

              const distance = Math.abs(rect.top - referencePoint);

              if (distance < bestDistance) {
                bestDistance = distance;
                bestSection = section;
              }
            });

            setActiveLink(bestSection.id);
          };

          if (sections.length && links.length) {
            if ('IntersectionObserver' in window) {
              const observer = new IntersectionObserver((entries) => {
                const visibleEntries = entries
                  .filter((entry) => entry.isIntersecting)
                  .sort((a, b) => {
                    const aDistance = Math.abs(a.target.getBoundingClientRect().top - getReferencePoint());
                    const bDistance = Math.abs(b.target.getBoundingClientRect().top - getReferencePoint());
                    return aDistance - bDistance;
                  });

                if (visibleEntries.length > 0) {
                  setActiveLink(visibleEntries[0].target.id);
                }
              }, {
                root: null,
                threshold: [0.15, 0.35, 0.5, 0.7],
                rootMargin: '-12% 0px -35% 0px'
              });

              sections.forEach((section) => observer.observe(section));
            }

            const refresh = () => requestAnimationFrame(() => {
              if (manualOverride) {
                setActiveLink(manualOverride);
                return;
              }

              updateFromVisibleSection();
            });

            const clearManualOverride = () => {
              manualOverride = null;
              updateFromVisibleSection();
            };

            window.addEventListener('scroll', refresh, { passive: true });
            window.addEventListener('resize', refresh);
            window.addEventListener('wheel', clearManualOverride, { passive: true });
            window.addEventListener('touchmove', clearManualOverride, { passive: true });
            window.addEventListener('keydown', (event) => {
              if (['ArrowDown', 'ArrowUp', 'PageDown', 'PageUp', 'Home', 'End', ' ', 'Spacebar'].includes(event.key)) {
                clearManualOverride();
              }
            });

            links.forEach((link) => {
              link.addEventListener('click', (event) => {
                const targetId = link.dataset.target;
                if (!targetId) {
                  return;
                }

                const target = document.getElementById(targetId);
                if (!target) {
                  return;
                }

                event.preventDefault();
                manualOverride = targetId;
                setActiveLink(targetId);
                window.history.pushState(null, '', `#${targetId}`);
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
              });
            });

            updateFromVisibleSection();
          }
        };

        if (document.readyState === 'loading') {
          document.addEventListener('DOMContentLoaded', init);
        } else {
          init();
        }
      })();
    </script>

  </body>

</html>
