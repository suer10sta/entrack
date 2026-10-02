<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __('messages.privacy_meta_title') }}</title>
    <meta name="description" content="{{ __('messages.privacy_meta_desc') }}">
    <meta name="robots" content="index,follow">
    <meta name="theme-color" content="#0b2638">
    <link rel="canonical" href="{{ route('confidentialite') }}">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='15' fill='%230b2638'/%3E%3Cpath d='M18 16h14c11 0 18 6 18 16s-7 16-18 16H18V16Zm12 23c6 0 10-2 10-7s-4-7-10-7h-2v14h2Z' fill='%238ccfd0'/%3E%3C/svg%3E">

    <meta property="og:title" content="{{ __('messages.privacy_og_title') }}">
    <meta property="og:description" content="{{ __('messages.privacy_og_desc') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('confidentialite') }}">

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
      $privacyEmailLink = '<a href="mailto:support@dalytrac.com" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">support@dalytrac.com</a>';
      $privacyAuthorityLink = '<a href="https://www.autoriteprotectiondonnees.be" target="_blank" rel="noopener" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">' . __('messages.privacy_s7_authority') . '</a>';
    @endphp

    <main id="main">
      <div class="border-b border-[#e0e8e4] bg-[#f4f7f5] pb-[61px] pt-[69px] max-md:pb-[49px] max-md:pt-[52px]">
        <div class="mx-auto max-w-[1200px] px-6 max-md:px-4">
          <p class="mb-[14px] text-[0.72rem] font-bold uppercase tracking-[0.17em] text-brand-secondary">{{ __('messages.privacy_eyebrow') }}</p>
          <h1 class="text-[clamp(2.9rem,5vw,4rem)] font-semibold leading-[1.02] tracking-[-0.052em] text-brand-primary max-sm:text-[clamp(2.5rem,11vw,3.7rem)]">{{ __('messages.privacy_title') }}</h1>
          <p class="mt-[17px] max-w-[730px] text-[1.02rem] text-[#5b6b73]">{{ __('messages.privacy_intro') }}</p>
        </div>
      </div>

      <div class="mx-auto grid max-w-[1200px] items-start gap-[76px] px-6 pb-[110px] pt-[62px] max-md:gap-12 max-md:px-4 max-md:pb-[78px] max-md:pt-[42px] lg:grid-cols-[285px_minmax(0,1fr)]">
        <aside class="lg:sticky lg:top-[130px]" aria-label="{{ __('messages.privacy_nav_label') }}">
          <p class="mb-[17px] text-[0.7rem] font-bold uppercase tracking-[0.12em] text-[#6e8584]">{{ __('messages.privacy_nav_heading') }}</p>
          <nav aria-label="{{ __('messages.privacy_nav_sections') }}" class="grid border-t border-[#dce6e4] max-sm:grid-cols-1 sm:grid-cols-2 sm:gap-x-[19px] lg:grid-cols-1">
            <a href="#responsable" data-target="responsable" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.privacy_nav_controller') }}</span>
            </a>
            <a href="#traitements" data-target="traitements" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.privacy_nav_processing') }}</span>
            </a>
            <a href="#obligatoires" data-target="obligatoires" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.privacy_nav_required') }}</span>
            </a>
            <a href="#destinataires" data-target="destinataires" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.privacy_nav_recipients') }}</span>
            </a>
            <a href="#transferts" data-target="transferts" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.privacy_nav_transfers') }}</span>
            </a>
            <a href="#conservation" data-target="conservation" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.privacy_nav_retention') }}</span>
            </a>
            <a href="#droits" data-target="droits" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.privacy_nav_rights') }}</span>
            </a>
            <a href="#securite" data-target="securite" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.privacy_nav_security') }}</span>
            </a>
            <a href="#mise-a-jour" data-target="mise-a-jour" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.privacy_nav_updates') }}</span>
            </a>
          </nav>
        </aside>

        <div class="min-w-0">
          <section id="responsable" aria-labelledby="responsable-title" class="scroll-mt-[110px]">
            <h2 id="responsable-title" class="text-[clamp(1.65rem,2vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{!! __('messages.privacy_s1_title') !!}</h2>
            <div class="mt-[18px] rounded-lg border border-[#dce6e4] bg-[#f8faf9] p-5 text-[0.93rem] leading-[1.75] text-[#50666c]">
              <p class="m-0">{{ __('messages.privacy_s1_box1') }}</p>
              <p class="mt-3">{!! __('messages.privacy_s1_box2', ['email' => $privacyEmailLink]) !!}</p>
            </div>
          </section>

          <section id="traitements" aria-labelledby="traitements-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="traitements-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{!! __('messages.privacy_s2_title') !!}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.privacy_s2_intro') }}</p>
            <div class="mt-[18px] overflow-x-auto rounded-lg border border-[#dce6e4]" role="region" aria-label="{{ __('messages.privacy_s2_table_label') }}" tabindex="0">
              <table class="w-full min-w-[760px] border-collapse text-left">
                <thead class="bg-brand-primary text-white">
                  <tr>
                    <th scope="col" class="p-4 text-[0.72rem] font-bold uppercase tracking-[0.08em]">{{ __('messages.privacy_s2_th_journey') }}</th>
                    <th scope="col" class="p-4 text-[0.72rem] font-bold uppercase tracking-[0.08em]">{{ __('messages.privacy_s2_th_data') }}</th>
                    <th scope="col" class="p-4 text-[0.72rem] font-bold uppercase tracking-[0.08em]">{{ __('messages.privacy_s2_th_purpose') }}</th>
                    <th scope="col" class="p-4 text-[0.72rem] font-bold uppercase tracking-[0.08em]">{{ __('messages.privacy_s2_th_basis') }}</th>
                    <th scope="col" class="p-4 text-[0.72rem] font-bold uppercase tracking-[0.08em]">{{ __('messages.privacy_s2_th_duration') }}</th>
                  </tr>
                </thead>
                <tbody class="text-[0.85rem] leading-[1.6] text-[#1b3444]">
                  <tr class="border-b border-[#eef3f1]">
                    <td class="p-4 font-bold text-brand-primary">{{ __('messages.privacy_s2_r1_c1') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r1_c2') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r1_c3') }}</td>
                    <td class="p-4 font-medium text-brand-secondary">{{ __('messages.privacy_s2_r1_c4') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r1_c5') }}</td>
                  </tr>
                  <tr class="border-b border-[#eef3f1] bg-[#f8faf9]">
                    <td class="p-4 font-bold text-brand-primary">{{ __('messages.privacy_s2_r2_c1') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r2_c2') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r2_c3') }}</td>
                    <td class="p-4 font-medium text-brand-secondary">{{ __('messages.privacy_s2_r2_c4') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r2_c5') }}</td>
                  </tr>
                  <tr class="border-b border-[#eef3f1]">
                    <td class="p-4 font-bold text-brand-primary">{{ __('messages.privacy_s2_r3_c1') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r3_c2') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r3_c3') }}</td>
                    <td class="p-4 font-medium text-brand-secondary">{{ __('messages.privacy_s2_r3_c4') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r3_c5') }}</td>
                  </tr>
                  <tr class="border-b border-[#eef3f1] bg-[#f8faf9]">
                    <td class="p-4 font-bold text-brand-primary">{{ __('messages.privacy_s2_r4_c1') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r4_c2') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r4_c3') }}</td>
                    <td class="p-4 font-medium text-brand-secondary">{{ __('messages.privacy_s2_r4_c4') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r4_c5') }}</td>
                  </tr>
                  <tr class="border-b border-[#eef3f1]">
                    <td class="p-4 font-bold text-brand-primary">{{ __('messages.privacy_s2_r5_c1') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r5_c2') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r5_c3') }}</td>
                    <td class="p-4 font-medium text-brand-secondary">{{ __('messages.privacy_s2_r5_c4') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r5_c5') }}</td>
                  </tr>
                  <tr class="border-b border-[#eef3f1] bg-[#f8faf9]">
                    <td class="p-4 font-bold text-brand-primary">{{ __('messages.privacy_s2_r6_c1') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r6_c2') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r6_c3') }}</td>
                    <td class="p-4 font-medium text-brand-secondary">{{ __('messages.privacy_s2_r6_c4') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r6_c5') }}</td>
                  </tr>
                  <tr>
                    <td class="p-4 font-bold text-brand-primary">{{ __('messages.privacy_s2_r7_c1') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r7_c2') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r7_c3') }}</td>
                    <td class="p-4 font-medium text-brand-secondary">{{ __('messages.privacy_s2_r7_c4') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.privacy_s2_r7_c5') }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <section id="obligatoires" aria-labelledby="obligatoires-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="obligatoires-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{!! __('messages.privacy_s3_title') !!}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.privacy_s3_text') }}</p>
          </section>

          <section id="destinataires" aria-labelledby="destinataires-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="destinataires-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{!! __('messages.privacy_s4_title') !!}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.privacy_s4_p1') }}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.privacy_s4_p2') }}</p>
          </section>

          <section id="transferts" aria-labelledby="transferts-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="transferts-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{!! __('messages.privacy_s5_title') !!}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.privacy_s5_text') }}</p>
          </section>

          <section id="conservation" aria-labelledby="conservation-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="conservation-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{!! __('messages.privacy_s6_title') !!}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.privacy_s6_text') }}</p>
          </section>

          <section id="droits" aria-labelledby="droits-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="droits-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{!! __('messages.privacy_s7_title') !!}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.privacy_s7_p1') }}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{!! __('messages.privacy_s7_p2', ['email' => $privacyEmailLink]) !!}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{!! __('messages.privacy_s7_p3', ['authority' => $privacyAuthorityLink]) !!}</p>
          </section>

          <section id="securite" aria-labelledby="securite-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="securite-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{!! __('messages.privacy_s8_title') !!}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.privacy_s8_text') }}</p>
          </section>

          <section id="mise-a-jour" aria-labelledby="mise-a-jour-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="mise-a-jour-title" class="text-[clamp(1.65rem,2.4vw,1.8rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.privacy_s9_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.privacy_s9_text') }}</p>
            <p class="mt-3 text-[0.8rem] text-[#77908d]">{{ __('messages.privacy_s9_updated') }}</p>
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
