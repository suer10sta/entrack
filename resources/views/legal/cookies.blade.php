<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

  <head>
    <x-public.head />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <x-seo :title="__('messages.cookies_meta_title')" :description="__('messages.cookies_meta_desc')" />
    <meta name="theme-color" content="#0b2638">

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
          <p class="mb-[14px] text-[0.72rem] font-bold uppercase tracking-[0.17em] text-brand-secondary">{{ __('messages.cookies_kicker') }}</p>
          <h1 class="text-[clamp(2.9rem,5vw,4rem)] font-semibold leading-[1.02] tracking-[-0.052em] text-brand-primary max-sm:text-[clamp(2.5rem,11vw,3.7rem)]">{{ __('messages.cookies_title') }}</h1>
          <p class="mt-[17px] max-w-[730px] text-[1.02rem] text-[#5b6b73]">{{ __('messages.cookies_lead') }}</p>
        </div>
      </div>

      <div class="mx-auto grid max-w-[1200px] items-start gap-[76px] px-6 pb-[110px] pt-[62px] max-md:gap-12 max-md:px-4 max-md:pb-[78px] max-md:pt-[42px] lg:grid-cols-[285px_minmax(0,1fr)]">
        <aside class="lg:sticky lg:top-[112px]" aria-label="{{ __('messages.cookies_nav_label') }}">
          <p class="mb-[17px] text-[0.7rem] font-bold uppercase tracking-[0.12em] text-[#6e8584]">{{ __('messages.cookies_nav_heading') }}</p>
          <nav aria-label="{{ __('messages.cookies_nav_label') }}" class="grid border-t border-[#dce6e4] max-sm:grid-cols-1 sm:grid-cols-2 sm:gap-x-[19px] lg:grid-cols-1">
            <a href="#definition" data-target="definition" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cookies_nav_definition') }}</span>
            </a>
            <a href="#responsable" data-target="responsable" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cookies_nav_responsable') }}</span>
            </a>
            <a href="#categories" data-target="categories" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cookies_nav_categories') }}</span>
            </a>
            <a href="#inventaire" data-target="inventaire" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cookies_nav_inventaire') }}</span>
            </a>
            <a href="#choix" data-target="choix" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cookies_nav_choix') }}</span>
            </a>
            <a href="#duree" data-target="duree" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cookies_nav_duree') }}</span>
            </a>
            <a href="#navigateurs" data-target="navigateurs" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cookies_nav_navigateurs') }}</span>
            </a>
            <a href="#maj" data-target="maj" class="docs-nav-link group relative flex min-h-[52px] items-center border-b border-[#dce6e4] pl-[14px] text-[0.85rem] font-medium text-brand-primary no-underline transition-all duration-300 hover:text-brand-secondary">
              <span class="absolute left-0 top-1/2 h-0 w-[2px] -translate-y-1/2 rounded-full bg-brand-secondary transition-all duration-300"></span>
              <span class="relative">{{ __('messages.cookies_nav_maj') }}</span>
            </a>
          </nav>
        </aside>

        <div class="min-w-0" x-data="{ panel: false, prefs: true, audience: window.DalyTracConsent ? window.DalyTracConsent.allowed() : false, saved: false, save() { try { localStorage.setItem('dalytrac_cookie_consent', JSON.stringify({ necessary: true, preferences: this.prefs, audience: this.audience, at: new Date().toISOString() })); } catch (e) {} if (window.DalyTracConsent) window.DalyTracConsent.choose(this.audience ? 'all' : 'necessary', this.prefs); this.saved = true; }, refuse() { this.prefs = false; this.audience = false; this.save(); } }">
          <section id="definition" aria-labelledby="definition-title" class="scroll-mt-[110px]">
            <h2 id="definition-title" class="text-[clamp(1.65rem,2.4vw,2rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cookies_s1_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cookies_s1_p1') }}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cookies_s1_p2') }}</p>
          </section>

          <section id="responsable" aria-labelledby="responsable-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="responsable-title" class="text-[clamp(1.65rem,2.4vw,2rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cookies_s2_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cookies_s2_p1') }} <a href="mailto:support@dalytrac.com" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">support@dalytrac.com</a>.</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cookies_s2_p2') }}</p>
          </section>

          <section id="categories" aria-labelledby="categories-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="categories-title" class="text-[clamp(1.65rem,2.4vw,2rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cookies_s3_title') }}</h2>
            <div class="mt-[18px] overflow-hidden rounded-lg border border-[#dce6e4]">
              <div class="border-b border-[#eef3f1] bg-[#f8faf9] p-5">
                <h3 class="text-[0.95rem] font-semibold text-brand-primary">{{ __('messages.cookies_cat1_t') }}</h3>
                <p class="mt-1.5 text-[0.88rem] leading-[1.65] text-[#50666c]">{{ __('messages.cookies_cat1_d') }}</p>
              </div>
              <div class="border-b border-[#eef3f1] p-5">
                <h3 class="text-[0.95rem] font-semibold text-brand-primary">{{ __('messages.cookies_cat2_t') }}</h3>
                <p class="mt-1.5 text-[0.88rem] leading-[1.65] text-[#50666c]">{{ __('messages.cookies_cat2_d') }}</p>
              </div>
              <div class="border-b border-[#eef3f1] bg-[#f8faf9] p-5">
                <h3 class="text-[0.95rem] font-semibold text-brand-primary">{{ __('messages.cookies_cat3_t') }}</h3>
                <p class="mt-1.5 text-[0.88rem] leading-[1.65] text-[#50666c]">{{ __('messages.cookies_cat3_d') }}</p>
              </div>
              <div class="p-5">
                <h3 class="text-[0.95rem] font-semibold text-brand-primary">{{ __('messages.cookies_cat4_t') }}</h3>
                <p class="mt-1.5 text-[0.88rem] leading-[1.65] text-[#50666c]">{{ __('messages.cookies_cat4_d') }}</p>
              </div>
            </div>
          </section>

          <section id="inventaire" aria-labelledby="inventaire-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="inventaire-title" class="text-[clamp(1.65rem,2.4vw,2rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cookies_s4_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cookies_s4_intro') }}</p>
            <div class="mt-[18px] overflow-x-auto rounded-lg border border-[#dce6e4]" role="region" aria-label="{{ __('messages.cookies_s4_title') }}" tabindex="0">
              <table class="w-full min-w-[720px] border-collapse text-left">
                <thead class="bg-brand-primary text-white">
                  <tr>
                    <th scope="col" class="p-4 text-[0.72rem] font-bold uppercase tracking-[0.08em]">{{ __('messages.cookies_th_name') }}</th>
                    <th scope="col" class="p-4 text-[0.72rem] font-bold uppercase tracking-[0.08em]">{{ __('messages.cookies_th_provider') }}</th>
                    <th scope="col" class="p-4 text-[0.72rem] font-bold uppercase tracking-[0.08em]">{{ __('messages.cookies_th_purpose') }}</th>
                    <th scope="col" class="p-4 text-[0.72rem] font-bold uppercase tracking-[0.08em]">{{ __('messages.cookies_th_category') }}</th>
                    <th scope="col" class="p-4 text-[0.72rem] font-bold uppercase tracking-[0.08em]">{{ __('messages.cookies_th_duration') }}</th>
                  </tr>
                </thead>
                <tbody class="text-[0.85rem] leading-[1.6] text-[#1b3444]">
                  <tr class="border-b border-[#eef3f1]">
                    <td class="p-4 font-bold text-brand-secondary">laravel_session</td>
                    <td class="p-4 text-[#50666c]">DalyTrac</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.cookies_row_session_u') }}</td>
                    <td class="p-4 font-medium text-brand-secondary">{{ __('messages.cookies_cat_necessary') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.cookies_dur_session') }}</td>
                  </tr>
                  <tr class="border-b border-[#eef3f1] bg-[#f8faf9]">
                    <td class="p-4 font-bold text-brand-secondary">XSRF-TOKEN</td>
                    <td class="p-4 text-[#50666c]">DalyTrac</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.cookies_row_xsrf_u') }}</td>
                    <td class="p-4 font-medium text-brand-secondary">{{ __('messages.cookies_cat_necessary') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.cookies_dur_session') }}</td>
                  </tr>
                  <tr class="border-b border-[#eef3f1]">
                    <td class="p-4 font-bold text-brand-secondary">remember_web_*</td>
                    <td class="p-4 text-[#50666c]">DalyTrac</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.cookies_row_remember_u') }}</td>
                    <td class="p-4 font-medium text-brand-secondary">{{ __('messages.cookies_cat_necessary') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.cookies_dur_remember') }}</td>
                  </tr>
                  <tr class="border-b border-[#eef3f1] bg-[#f8faf9]">
                    <td class="p-4 font-bold text-brand-secondary">{{ __('messages.cookies_row_locale_n') }}</td>
                    <td class="p-4 text-[#50666c]">DalyTrac</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.cookies_row_locale_u') }}</td>
                    <td class="p-4 font-medium text-brand-secondary">{{ __('messages.cookies_cat_preferences') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.cookies_dur_session') }}</td>
                  </tr>
                  <tr>
                    <td class="p-4 font-bold text-brand-secondary">__stripe_mid / __stripe_sid</td>
                    <td class="p-4 text-[#50666c]">Stripe Inc.</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.cookies_row_stripe_u') }}</td>
                    <td class="p-4 font-medium text-brand-secondary">{{ __('messages.cookies_cat_necessary') }}</td>
                    <td class="p-4 text-[#50666c]">{{ __('messages.cookies_dur_stripe') }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <section id="choix" aria-labelledby="choix-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="choix-title" class="text-[clamp(1.65rem,2.4vw,2rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cookies_s5_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cookies_s5_p1') }}</p>
            <div class="mt-5 flex flex-wrap gap-3">
              <button type="button" @click="panel = !panel" class="inline-flex min-h-[48px] cursor-pointer items-center justify-center gap-2 rounded-full border border-transparent btn-sweep btn-sweep-navy bg-brand-accent px-6 text-[0.88rem] font-bold text-brand-primary no-underline shadow-[0_12px_28px_rgba(231,184,75,0.2)] transition-all duration-300 ease-out hover:-translate-y-0.5 hover:text-white hover:shadow-[0_16px_34px_rgba(231,184,75,0.3)] max-sm:w-full">{{ __('messages.cookies_manage_btn') }}</button>
              <button type="button" @click="refuse()" class="inline-flex min-h-[48px] cursor-pointer items-center justify-center gap-2 rounded-full border border-[#cddfda] bg-white px-6 text-[0.88rem] font-bold text-brand-primary transition-all duration-300 ease-out hover:-translate-y-0.5 hover:border-brand-secondary hover:text-brand-secondary max-sm:w-full">{{ __('messages.cookies_refuse_btn') }}</button>
            </div>
            <div x-show="panel" x-cloak x-transition class="mt-5 rounded-lg border border-[#dce6e4] bg-[#f8faf9] p-5">
              <div class="flex items-center justify-between gap-4 border-b border-[#eef3f1] py-3">
                <div><p class="text-[0.9rem] font-bold text-brand-primary">{{ __('messages.cookies_necessary_t') }}</p><p class="text-[0.8rem] text-[#50666c]">{{ __('messages.cookies_necessary_d') }}</p></div>
                <span class="rounded-full bg-brand-secondary/10 px-3 py-1 text-[0.72rem] font-bold text-brand-secondary">{{ __('messages.cookies_always_on') }}</span>
              </div>
              <label class="flex cursor-pointer items-center justify-between gap-4 border-b border-[#eef3f1] py-3">
                <span><span class="block text-[0.9rem] font-bold text-brand-primary">{{ __('messages.cookies_pref_t') }}</span><span class="block text-[0.8rem] text-[#50666c]">{{ __('messages.cookies_pref_d') }}</span></span>
                <input type="checkbox" x-model="prefs" class="h-5 w-5 shrink-0 accent-[#2D7080]">
              </label>
              <label class="flex cursor-pointer items-center justify-between gap-4 py-3">
                <span><span class="block text-[0.9rem] font-bold text-brand-primary">{{ __('messages.cookies_audience_t') }}</span><span class="block text-[0.8rem] text-[#50666c]">{{ __('messages.cookies_audience_d') }}</span></span>
                <input type="checkbox" x-model="audience" class="h-5 w-5 shrink-0 accent-[#2D7080]">
              </label>
              <div class="mt-2 flex flex-wrap items-center gap-3">
                <button type="button" @click="save()" class="inline-flex min-h-[44px] cursor-pointer items-center justify-center rounded-full bg-brand-secondary px-6 text-[0.85rem] font-bold text-white transition-all duration-300 hover:-translate-y-0.5">{{ __('messages.cookies_save_btn') }}</button>
                <p x-show="saved" x-cloak class="text-[0.82rem] font-medium text-brand-secondary">{{ __('messages.cookies_saved_msg') }}</p>
              </div>
            </div>
            <p class="mt-4 text-[0.85rem] leading-[1.7] text-[#50666c]">{{ __('messages.cookies_refuse_note') }}</p>
          </section>

          <section id="duree" aria-labelledby="duree-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="duree-title" class="text-[clamp(1.65rem,2.4vw,2rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cookies_s6_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cookies_s6_p1') }}</p>
          </section>

          <section id="navigateurs" aria-labelledby="navigateurs-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="navigateurs-title" class="text-[clamp(1.65rem,2.4vw,2rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cookies_s7_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cookies_s7_p1') }}</p>
            <ul class="mt-3 list-disc space-y-1.5 pl-5 text-[0.9rem] text-[#50666c]">
              <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">Google Chrome</a></li>
              <li><a href="https://support.mozilla.org/fr/kb/activer-desactiver-cookies-preferences-sites" target="_blank" rel="noopener" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">Mozilla Firefox</a></li>
              <li><a href="https://support.apple.com/fr-fr/guide/safari/sfri11471/mac" target="_blank" rel="noopener" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">Apple Safari</a></li>
              <li><a href="https://support.microsoft.com/fr-fr/microsoft-edge/supprimer-les-cookies-dans-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">Microsoft Edge</a></li>
            </ul>
          </section>

          <section id="maj" aria-labelledby="maj-title" class="mt-[71px] border-t border-[#eef3f1] pt-[52px] scroll-mt-[110px] max-md:mt-[52px]">
            <h2 id="maj-title" class="text-[clamp(1.65rem,2.4vw,2rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ __('messages.cookies_s8_title') }}</h2>
            <p class="mt-4 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cookies_s8_p1') }}</p>
            <p class="mt-3 text-[0.8rem] text-[#77908d]">{{ __('messages.cookies_updated') }}</p>
            <p class="mt-3 text-[0.93rem] leading-[1.75] text-[#50666c]">{{ __('messages.cookies_s8_p2') }} <a href="{{ route('confidentialite') }}" class="font-medium text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">{{ __('messages.cookies_privacy_link') }}</a>.</p>
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
          let manualTimer = null;

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

          const getReferencePoint = () => window.innerHeight * 0.35;

          const updateFromVisibleSection = () => {
            if (!sections.length || !links.length) {
              return;
            }

            if (manualOverride) {
              setActiveLink(manualOverride);
              return;
            }

            let currentId = sections[0].id;
            let bestVisibility = -1;
            let bestDistance = Number.POSITIVE_INFINITY;
            const referencePoint = getReferencePoint();

            for (const section of sections) {
              const rect = section.getBoundingClientRect();
              const visibleHeight = Math.max(0, Math.min(rect.bottom, window.innerHeight) - Math.max(rect.top, 0));
              const visibility = rect.height > 0 ? visibleHeight / rect.height : 0;
              const isVisible = visibleHeight > 0;

              if (!isVisible) {
                continue;
              }

              const sectionCenter = rect.top + rect.height / 2;
              const sectionDistance = Math.abs(sectionCenter - referencePoint);

              if (visibility > bestVisibility || (Math.abs(visibility - bestVisibility) < 0.0001 && sectionDistance < bestDistance)) {
                currentId = section.id;
                bestVisibility = visibility;
                bestDistance = sectionDistance;
              }
            }

            setActiveLink(currentId);
          };

          if (sections.length && links.length) {
            if ('IntersectionObserver' in window) {
              const observer = new IntersectionObserver((entries) => {
                const visibleEntries = entries
                  .filter((entry) => entry.isIntersecting)
                  .sort((a, b) => {
                    const aRect = a.target.getBoundingClientRect();
                    const bRect = b.target.getBoundingClientRect();
                    const aVisible = Math.max(0, Math.min(aRect.bottom, window.innerHeight) - Math.max(aRect.top, 0));
                    const bVisible = Math.max(0, Math.min(bRect.bottom, window.innerHeight) - Math.max(bRect.top, 0));
                    const aRatio = aRect.height > 0 ? aVisible / aRect.height : 0;
                    const bRatio = bRect.height > 0 ? bVisible / bRect.height : 0;

                    if (bRatio !== aRatio) {
                      return bRatio - aRatio;
                    }

                    const aDistance = Math.abs(aRect.top + aRect.height / 2 - getReferencePoint());
                    const bDistance = Math.abs(bRect.top + bRect.height / 2 - getReferencePoint());
                    return aDistance - bDistance;
                  });

                if (visibleEntries.length > 0 && !manualOverride) {
                  setActiveLink(visibleEntries[0].target.id);
                }
              }, {
                root: null,
                threshold: [0.15, 0.3, 0.5, 0.7],
                rootMargin: '-12% 0px -35% 0px'
              });

              sections.forEach((section) => observer.observe(section));
            }

            const clearManualOverride = () => {
              manualOverride = null;
              if (manualTimer) {
                clearTimeout(manualTimer);
                manualTimer = null;
              }
              updateFromVisibleSection();
            };

            const refresh = () => requestAnimationFrame(() => {
              if (manualOverride) {
                setActiveLink(manualOverride);
                return;
              }
              updateFromVisibleSection();
            });

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

                manualOverride = targetId;
                setActiveLink(targetId);
                if (manualTimer) {
                  clearTimeout(manualTimer);
                }
                manualTimer = window.setTimeout(() => {
                  clearManualOverride();
                }, 5000);
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
