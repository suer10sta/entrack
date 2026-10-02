<div
    x-data="{
        open: (() => {
            try {
                return !localStorage.getItem('dalytrac_cookie_banner_state');
            } catch (e) {
                return true;
            }
        })(),
        save(choice) {
            try {
                localStorage.setItem(
                    'dalytrac_cookie_banner_state',
                    JSON.stringify({ choice, timestamp: new Date().toISOString() })
                );
            } catch (e) {}
            this.open = false;
        }
    }"
    x-show="open"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-5"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-5"
    class="fixed inset-x-0 bottom-0 z-[80] px-4 pb-4"
>
    <div class="mx-auto max-w-[1180px] overflow-hidden rounded-[24px] border border-[#d9e7ed] bg-[#f7fafb]/95 shadow-[0_22px_56px_rgba(4,20,37,0.12)] backdrop-blur-md">
        <div class="flex items-center gap-3 border-b border-[#e6edf0] bg-[#eef7f9] px-4 py-3 sm:px-5">
            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-[#5ec8d4]"></span>
            <span class="text-[0.68rem] font-semibold uppercase tracking-[0.18em] text-[#264a5b]">Cookie settings</span>
        </div>

        <div class="flex flex-col gap-5 px-4 py-4 sm:px-5 lg:flex-row lg:items-center lg:justify-between lg:gap-8">
            <div class="min-w-0 lg:max-w-[62%]">
                <h2 class="text-[1.08rem] font-semibold tracking-[-0.04em] text-[#091b2c]">{{ __('messages.cookie_banner_title') }}</h2>
                <p class="mt-2 text-[0.87rem] leading-6 text-[#4f6873]">{{ __('messages.cookie_banner_text') }}</p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap lg:justify-end">
                <a href="{{ route('cookies') }}" class="inline-flex min-h-[42px] items-center justify-center rounded-full border border-[#d4dee3] bg-white px-4 text-[0.8rem] font-semibold text-[#0d2337] no-underline transition-all duration-200 hover:border-[#8ccfd0] hover:text-[#0e7888]">
                    {{ __('messages.cookie_banner_learn_more') }}
                </a>
                <button type="button" @click="save('rejected')" class="inline-flex min-h-[42px] items-center justify-center rounded-full border border-[#d4dee3] bg-white px-4 text-[0.8rem] font-semibold text-[#0d2337] transition-all duration-200 hover:border-[#8ccfd0] hover:text-[#0e7888]">
                    {{ __('messages.cookie_banner_reject_all') }}
                </button>
                <button type="button" @click="save('necessary')" class="inline-flex min-h-[42px] items-center justify-center rounded-full border border-[#d4dee3] bg-white px-4 text-[0.8rem] font-semibold text-[#0d2337] transition-all duration-200 hover:border-[#8ccfd0] hover:text-[#0e7888]">
                    {{ __('messages.cookie_banner_necessary_only') }}
                </button>
                <button type="button" @click="save('all')" class="inline-flex min-h-[42px] items-center justify-center rounded-full bg-[#0d2337] px-5 text-[0.8rem] font-semibold text-white shadow-[0_10px_20px_rgba(13,35,55,0.16)] transition-all duration-200 hover:-translate-y-0.5 hover:bg-[#16324a]">
                    {{ __('messages.cookie_banner_accept_all') }}
                </button>
            </div>
        </div>
    </div>
</div>
