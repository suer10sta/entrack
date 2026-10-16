{{-- Shared public Footer (single source of truth for all public marketing pages).
     Design: Home page footer, preserved as-is. Reused via <x-public.footer />. --}}
<footer class="bg-[#041425] pb-6 pt-[78px] text-[#a9bdcf]">
  <div class="mx-auto grid max-w-[1200px] gap-[62px] px-6 max-md:px-4 max-sm:grid-cols-1 sm:grid-cols-2 lg:grid-cols-[1.7fr_1fr_1fr_1fr] lg:gap-8">
    <div>
      <a href="#top" class="inline-flex items-baseline text-[1.65rem] font-extrabold tracking-[-0.065em] text-white no-underline"><x-application-logo style="background:#F4F7F6;border-radius:8px;padding:7px 10px;display:inline-flex;align-items:baseline;white-space:nowrap;font-family:Figtree,sans-serif;font-weight:800;letter-spacing:-0.065em;line-height:1.15" /></a>
      <p class="mt-5 max-w-[330px] text-[0.88rem]">{{ __('messages.public_footer_brand_desc') }}</p>
    </div>
    <div class="flex flex-col items-start">
      <h2 class="mb-[18px] mt-1 text-[0.76rem] uppercase tracking-[0.12em] text-white">{{ __('messages.public_footer_product') }}</h2>
      <a href="{{ route('welcome') }}#produit" class="my-[5px] text-[0.86rem] text-[#9db2c4] no-underline transition-colors duration-300 hover:text-[#8ccfd0]">{{ __('messages.public_footer_features') }}</a>
      <a href="{{ route('tarifs') }}" class="my-[5px] text-[0.86rem] text-[#9db2c4] no-underline transition-colors duration-300 hover:text-[#8ccfd0]">{{ __('messages.public_nav_pricing') }}</a>
      <a href="{{ route('demo') }}" class="my-[5px] text-[0.86rem] text-[#9db2c4] no-underline transition-colors duration-300 hover:text-[#8ccfd0]">{{ __('messages.public_nav_demo') }}</a>
      <a href="{{ route('login') }}" class="my-[5px] text-[0.86rem] text-[#9db2c4] no-underline transition-colors duration-300 hover:text-[#8ccfd0]">{{ __('messages.public_nav_login') }}</a>
    </div>
    <div class="flex flex-col items-start">
      <h2 class="mb-[18px] mt-1 text-[0.76rem] uppercase tracking-[0.12em] text-white">{{ __('messages.public_footer_resources') }}</h2>
      <a href="{{ route('faq') }}" class="my-[5px] text-[0.86rem] text-[#9db2c4] no-underline transition-colors duration-300 hover:text-[#8ccfd0]">{{ __('messages.public_footer_faq') }}</a>
      <a href="{{ route('contact') }}" class="my-[5px] text-[0.86rem] text-[#9db2c4] no-underline transition-colors duration-300 hover:text-[#8ccfd0]">{{ __('messages.public_footer_contact') }}</a>
    </div>
    <div class="flex flex-col items-start">
      <h2 class="mb-[18px] mt-1 text-[0.76rem] uppercase tracking-[0.12em] text-white">{{ __('messages.public_footer_legal') }}</h2>
      <a href="{{ route('mentions-legales') }}" class="my-[5px] text-[0.86rem] text-[#9db2c4] no-underline transition-colors duration-300 hover:text-[#8ccfd0]">{{ __('messages.public_footer_legal_mentions') }}</a>
      <a href="{{ route('confidentialite') }}" class="my-[5px] text-[0.86rem] text-[#9db2c4] no-underline transition-colors duration-300 hover:text-[#8ccfd0]">{{ __('messages.public_footer_privacy') }}</a>
      <a href="{{ route('cookies') }}" class="my-[5px] text-[0.86rem] text-[#9db2c4] no-underline transition-colors duration-300 hover:text-[#8ccfd0]">{{ __('messages.public_footer_cookies') }}</a>
      <a href="{{ route('cgu') }}" class="my-[5px] text-[0.86rem] text-[#9db2c4] no-underline transition-colors duration-300 hover:text-[#8ccfd0]">{{ __('messages.public_footer_tos') }}</a>
      <a href="{{ route('cgv') }}" class="my-[5px] text-[0.86rem] text-[#9db2c4] no-underline transition-colors duration-300 hover:text-[#8ccfd0]">{{ __('messages.public_footer_tac') }}</a>
    </div>
  </div>
  <div class="mx-auto mt-[58px] flex max-w-[1200px] justify-between gap-[30px] border-t border-white/10 px-6 pt-[22px] text-[0.72rem] max-md:px-4 max-sm:flex-col">
    <span>{{ __('messages.public_footer_copyright') }}</span>
    <!-- <span>{{ __('messages.public_footer_design_note') }}</span> -->
  </div>
</footer>

<x-public.cookie-banner />
