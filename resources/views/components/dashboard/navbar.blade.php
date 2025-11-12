<header class="flex items-center justify-between px-6 py-3 bg-white border-b border-gray-200">
    
    <div class="flex items-center w-full max-w-lg">
        <div class="relative w-full">
            <span class="absolute inset-y-0 left-4 flex items-center pointer-events-none text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </span>
            {{-- CHANGED: rounded-xl to rounded-lg, border to border-gray-300, focus:border-primary to focus:border-blue-500 --}}
            <input type="text" placeholder="{{ __('messages.search_placeholder') }}" 
                   class="w-full pl-11 pr-4 py-2.5 border rounded-lg bg-white focus:ring-0 focus:border-blue-500 text-sm border-gray-300" />
        
                   <select onchange="window.location.href='{{ url('lang/change') }}?lang='+this.value">
    <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>English</option>
    <option value="fr" {{ app()->getLocale() == 'fr' ? 'selected' : '' }}>Français</option>
    <option value="es" {{ app()->getLocale() == 'es' ? 'selected' : '' }}>Español</option>
</select>

                </div>
    </div>

    <div class="flex items-center gap-4 ml-6">
        
        {{-- CHANGED: rounded-xl to rounded-lg, border to border-gray-300 --}}
        <button class="p-2.5 border border-gray-300 bg-white rounded-lg hover:bg-gray-50 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-600">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
            </svg>
        </button>

    </div>
</header>