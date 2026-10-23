<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

  <head>
    <x-favicon />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <x-seo :title="__('messages.public_faq_page_meta_title')" :description="__('messages.public_faq_page_meta_desc')" />
    <meta name="theme-color" content="#0b2638">

    @php
      $faqGroups = [
        [
          'id' => 'demarrer', 'index' => '01 / 04', 'title' => __('messages.public_faq_page_group_1_title'), 'nav' => __('messages.public_faq_page_group_1_nav'), 'count' => 3,
          'items' => [
            ['q' => __('messages.public_faq_page_q1'), 'a' => __('messages.public_faq_page_a1'), 'link' => [route('produit'), __('messages.public_faq_page_link_1')]],
            ['q' => __('messages.public_faq_page_q2'), 'a' => __('messages.public_faq_page_a2'), 'link' => [route('register'), __('messages.public_faq_page_link_2')]],
            ['q' => __('messages.public_faq_page_q3'), 'a' => __('messages.public_faq_page_a3'), 'visual' => [__('messages.public_faq_page_visual_label'), __('messages.public_faq_page_visual_title'), __('messages.public_faq_page_visual_text')], 'link' => [route('demo'), __('messages.public_faq_page_link_3')]],
          ],
        ],
        [
          'id' => 'projets-equipe', 'index' => '02 / 04', 'title' => __('messages.public_faq_page_group_2_title'), 'nav' => __('messages.public_faq_page_group_2_nav'), 'count' => 4,
          'items' => [
            ['q' => __('messages.public_faq_page_q4'), 'a' => __('messages.public_faq_page_a4'), 'link' => [route('produit'), __('messages.public_faq_page_link_4')]],
            ['q' => __('messages.public_faq_page_q5'), 'a' => __('messages.public_faq_page_a5')],
            ['q' => __('messages.public_faq_page_q6'), 'a' => __('messages.public_faq_page_a6'), 'link' => [route('produit'), __('messages.public_faq_page_link_5')]],
            ['q' => __('messages.public_faq_page_q7'), 'a' => __('messages.public_faq_page_a7'), 'link' => [route('tarifs'), __('messages.public_faq_page_link_6')]],
          ],
        ],
        [
          'id' => 'essai-tarifs', 'index' => '03 / 04', 'title' => __('messages.public_faq_page_group_3_title'), 'nav' => __('messages.public_faq_page_group_3_nav'), 'count' => 4,
          'items' => [
            ['q' => __('messages.public_faq_page_q8'), 'a' => __('messages.public_faq_page_a8'), 'link' => [route('register'), __('messages.public_faq_page_link_7')]],
            ['q' => __('messages.public_faq_page_q9'), 'a' => __('messages.public_faq_page_a9')],
            ['q' => __('messages.public_faq_page_q10'), 'a' => __('messages.public_faq_page_a10'), 'link' => [route('tarifs'), __('messages.public_faq_page_link_8')]],
            ['q' => __('messages.public_faq_page_q11'), 'a' => __('messages.public_faq_page_a11'), 'link' => [route('tarifs') . '#offres', __('messages.public_faq_page_link_9')]],
          ],
        ],
        [
          'id' => 'factures-donnees', 'index' => '04 / 04', 'title' => __('messages.public_faq_page_group_4_title'), 'nav' => __('messages.public_faq_page_group_4_nav'), 'count' => 3,
          'items' => [
            ['q' => __('messages.public_faq_page_q12'), 'a' => __('messages.public_faq_page_a12'), 'link' => [route('produit'), __('messages.public_faq_page_link_10')]],
            ['q' => __('messages.public_faq_page_q13'), 'a' => __('messages.public_faq_page_a13')],
            ['q' => __('messages.public_faq_page_q14'), 'a' => __('messages.public_faq_page_a14'), 'link' => [route('contact'), __('messages.public_faq_page_link_11')]],
          ],
        ],
      ];
      $faqLd = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect($faqGroups)->flatMap(fn ($g) => $g['items'])->map(fn ($i) => [
          '@type' => 'Question',
          'name' => html_entity_decode(strip_tags(str_replace('&nbsp;', ' ', $i['q']))),
          'acceptedAnswer' => ['@type' => 'Answer', 'text' => html_entity_decode(strip_tags(str_replace('&nbsp;', ' ', $i['a'])))],
        ])->values()->all(),
      ];
    @endphp
    <x-json-ld :data="$faqLd" />

    <!-- Vite Assets (Tailwind CSS) -->
    @vite('resources/css/app.css')

    <style>
      [x-cloak] { display: none !important; }
      .faq-summary::-webkit-details-marker { display: none; }
      html, body { overflow-x: clip; }
      @supports not (overflow: clip) {
        html, body { overflow-x: hidden; }
      }
    </style>
  </head>

  <body class="bg-white text-[#1b3444] antialiased">

    <x-public.header />

    <main id="main"
      data-count-all="{{ __('messages.public_faq_search_count') }}"
      data-found-single="{{ __('messages.public_faq_page_found_single') }}"
      data-found-plural="{{ __('messages.public_faq_page_found_plural') }}"
      x-data="{
      q: '',
      countAll: '',
      foundSingle: '',
      foundPlural: '',
      norm(v) { return v.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase(); },
      filter() {
        const query = this.norm(this.q.trim());
        let visible = 0;
        this.$el.querySelectorAll('.help-group').forEach(group => {
          let found = 0;
          group.querySelectorAll('.help-item').forEach(item => {
            const match = this.norm(item.textContent).includes(query);
            item.hidden = !match;
            if (match) { found++; visible++; }
          });
          group.hidden = found === 0;
        });
        this.$refs.noresult.hidden = visible !== 0;
        this.$refs.count.textContent = query ? `${visible} ${visible > 1 ? this.foundPlural : this.foundSingle}` : this.countAll;
        this.$refs.clear.hidden = !this.q;
      },
      reset() { this.q = ''; this.filter(); }
    }"
      x-init="countAll = $el.dataset.countAll; foundSingle = $el.dataset.foundSingle; foundPlural = $el.dataset.foundPlural">

      <div class="border-b border-[#e0e8e4] bg-[#f4f7f5] pb-[61px] pt-[69px] max-md:pb-[49px] max-md:pt-[52px]">
        <div class="mx-auto max-w-[1200px] px-6 max-md:px-4">
          <p class="mb-[14px] text-[0.72rem] font-bold uppercase tracking-[0.17em] text-brand-secondary">{{ __('messages.public_faq_page_eyebrow') }}</p>
          <h1 class="text-[clamp(2.9rem,5vw,4.8rem)] font-semibold leading-[1.02] tracking-[-0.052em] text-brand-primary max-sm:text-[clamp(2.5rem,11vw,3.7rem)]">{{ __('messages.public_faq_page_title') }}</h1>
          <p class="mt-[17px] max-w-[730px] text-[1.02rem] text-[#5b6b73]">{{ __('messages.public_faq_page_intro') }}</p>
          <div class="mt-[34px] max-w-[710px]">
            <label for="help-search" class="mb-[9px] block text-[0.87rem] font-bold text-brand-primary">{{ __('messages.public_faq_search_label') }}</label>
            <div class="flex min-h-[62px] items-center gap-[11px] rounded-lg border border-[#bbd2cc] bg-white px-4 shadow-[0_9px_22px_rgba(11,38,56,0.045)] focus-within:outline focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-brand-secondary">
              <span aria-hidden="true" class="text-brand-secondary"><i data-lucide="search" class="h-6 w-6"></i></span>
              <input id="help-search" x-ref="search" x-model="q" @input="filter()" type="search" placeholder="{{ __('messages.public_faq_search_placeholder') }}" autocomplete="off" aria-controls="help-answers" class="h-[57px] min-w-0 flex-1 border-0 bg-transparent text-[0.97rem] text-brand-primary outline-none placeholder:text-[#7d9093]">
              <button x-ref="clear" @click="reset(); $refs.search.focus()" type="button" hidden class="cursor-pointer border-0 bg-transparent p-[7px] text-[0.79rem] font-bold text-brand-secondary hover:text-[#245f6d]">{{ __('messages.public_faq_search_clear') }}</button>
            </div>
            <span x-ref="count" role="status" aria-live="polite" class="mt-[10px] block text-[0.78rem] text-[#54706e]">{{ __('messages.public_faq_search_count') }}</span>
          </div>
        </div>
      </div>

      <div class="mx-auto grid max-w-[1200px] items-start gap-[76px] px-6 pb-[110px] pt-[62px] max-md:gap-12 max-md:px-4 max-md:pb-[78px] max-md:pt-[42px] lg:grid-cols-[285px_minmax(0,1fr)]">
        <aside class="lg:sticky lg:top-[112px]" aria-label="{{ __('messages.public_faq_page_nav_label') }}">
          <p class="mb-[17px] text-[0.7rem] font-bold uppercase tracking-[0.12em] text-[#6e8584]">{{ __('messages.public_faq_navigate') }}</p>
          <nav aria-label="{{ __('messages.public_faq_eyebrow') }}" class="grid border-t border-[#dce6e4] max-sm:grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 lg:gap-0 lg:gap-x-0 sm:gap-x-[19px]">
            @foreach ($faqGroups as $i => $group)
              <a href="#{{ $group['id'] }}" @click="reset()" class="flex min-h-[64px] items-center gap-[11px] border-b border-[#dce6e4] text-[0.85rem] font-semibold text-brand-primary no-underline transition-colors duration-300 hover:text-brand-secondary max-sm:min-h-[52px]"><span class="text-[0.68rem] font-bold text-brand-secondary">0{{ $i + 1 }}</span> {{ $group['nav'] }} <b class="ml-auto text-[0.72rem] font-semibold text-[#77908d]">{{ $group['count'] }}</b></a>
            @endforeach
          </nav>
          <div class="mt-[35px] border-l-[3px] border-[#7bad9d] bg-[#edf5f1] p-[23px] max-md:mt-[22px]">
            <span class="text-[0.65rem] font-extrabold uppercase tracking-[0.1em] text-brand-secondary">{{ __('messages.public_faq_contact_prompt') }}</span>
            <p class="mb-[11px] mt-2 text-[1rem] font-semibold text-brand-primary">{{ __('messages.public_faq_contact_cta') }}</p>
            <a href="{{ route('contact') }}" class="group text-[0.81rem] font-bold text-brand-secondary transition-colors duration-300 hover:text-[#245f6d]">{{ __('messages.public_faq_contact_link') }} <span aria-hidden="true" class="inline-block transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"><i data-lucide="arrow-up-right" class="inline h-4 w-4"></i></span></a>
          </div>
        </aside>

        <div id="help-answers" class="min-w-0">
          <p x-ref="noresult" hidden class="border border-[#dce6e4] bg-[#f4f7f5] p-7 text-brand-primary">{{ __('messages.public_faq_no_result') }} <a href="{{ route('contact') }}" class="font-bold text-brand-secondary underline underline-offset-4">{{ __('messages.public_faq_no_result_link') }}</a>.</p>

          @foreach ($faqGroups as $group)
            <section id="{{ $group['id'] }}" aria-labelledby="{{ $group['id'] }}-title" class="help-group mb-[71px] scroll-mt-[110px] last:mb-0 max-md:mb-[52px]">
              <div class="mb-[18px] flex items-baseline gap-[17px] border-b-2 border-brand-primary pb-5">
                <span class="whitespace-nowrap text-[0.69rem] font-bold text-brand-secondary">{{ $group['index'] }}</span>
                <h2 id="{{ $group['id'] }}-title" class="text-[clamp(1.65rem,2.4vw,2.3rem)] font-semibold leading-[1.2] tracking-[-0.037em] text-brand-primary">{{ $group['title'] }}</h2>
              </div>
              <div>
                @foreach ($group['items'] as $item)
                  <details class="help-item group border-b border-[#dce6e4]">
                    <summary class="faq-summary flex min-h-[74px] cursor-pointer list-none items-center justify-between gap-[22px] text-[1rem] font-semibold leading-[1.35] text-brand-primary transition-colors duration-300 hover:text-brand-secondary max-sm:text-[0.92rem]">{!! $item['q'] !!}<span class="text-brand-secondary transition-transform duration-200 group-open:rotate-45"><i data-lucide="plus" class="h-6 w-6"></i></span></summary>
                    <div class="max-w-[710px] pb-[27px] pr-9 max-sm:pr-2">
                      <div class="[&_p]:m-0 [&_p]:text-[0.93rem] [&_p]:leading-[1.75] [&_p]:text-[#50666c] [&_strong]:text-brand-primary">{!! $item['a'] !!}</div>
                      @isset($item['visual'])
                        <div class="mt-[19px] flex min-h-[145px] max-w-[520px] flex-col justify-center rounded-[7px] border border-dashed border-[#8ca9a3] bg-[#eef5f2] p-[22px]">
                          <span class="text-[0.62rem] font-extrabold uppercase tracking-[0.11em] text-brand-secondary">{{ $item['visual'][0] }}</span>
                          <strong class="mt-[6px] text-[0.92rem] text-brand-primary">{{ $item['visual'][1] }}</strong>
                          <small class="mt-[3px] text-[0.74rem] text-[#5b6b73]">{{ $item['visual'][2] }}</small>
                        </div>
                      @endisset
                      @isset($item['link'])
                        <a href="{{ $item['link'][0] }}" class="group/link mt-[13px] inline-block text-[0.84rem] font-bold text-brand-secondary underline underline-offset-4 transition-colors duration-300 hover:text-[#245f6d]">{{ $item['link'][1] }} <span aria-hidden="true" class="inline-block transition-transform duration-300 group-hover/link:translate-x-0.5 group-hover/link:-translate-y-0.5"><i data-lucide="arrow-up-right" class="inline h-4 w-4"></i></span></a>
                      @endisset
                    </div>
                  </details>
                @endforeach
              </div>
            </section>
          @endforeach
        </div>
      </div>
    </main>

    <x-public.footer />

  </body>

</html>
