@props(['show' => false])

{{-- 
  This modal uses Alpine.js (x-data, x-show) to control visibility.
  'x-data' initializes the component, and 'show: @js($show)'
  safely passes the $showTrialExpiredModal variable from our middleware.
  'x-cloak' hides the element until Alpine.js is ready.
--}}
<div 
    x-data="{ show: @js($show) }" 
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    style="background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(3px);"
>
    {{-- Modal Panel --}}
    <div 
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        class="bg-white rounded-lg shadow-2xl w-full max-w-md"
    >
        {{-- Red Header --}}
        <div class="bg-red-600 text-white p-4 rounded-t-lg flex items-center">
            <svg class="h-8 w-8 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.083 3.903a9 9 0 1118 0 9 9 0 01-18 0zM12 9v.01" />
            </svg>
            <div>
                <h2 class="text-xl font-bold">Trial Period Ended</h2>
                <p class="text-sm text-red-100">Choose your plan to continue</p>
            </div>
        </div>

        {{-- Modal Body --}}
        <div class="p-6">
            <p class="text-gray-700 mb-6">
                Your 15-day free trial has ended. To continue using all features, please choose one of our plans:
            </p>

            {{-- Plan List --}}
            <div class="space-y-3 mb-8">
                <div class="bg-blue-50 border border-blue-200 p-3 rounded-lg flex items-center">
                    <span class="w-3 h-3 bg-blue-500 rounded-full mr-3"></span>
                    <span class="text-gray-800"><b>Solo:</b> €19/month - 1 user</span>
                </div>
                <div class="bg-green-50 border border-green-200 p-3 rounded-lg flex items-center">
                    <span class="w-3 h-3 bg-green-500 rounded-full mr-3"></span>
                    <span class="text-gray-800"><b>Premium:</b> €49/month - 1-10 users</span>
                </div>
                <div class="bg-yellow-50 border border-yellow-300 p-3 rounded-lg flex items-center">
                    <span class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></span>
                    <span class="text-gray-800"><b>Pro:</b> €99/month - Unlimited users</span>
                </div>
            </div>

            {{-- Button to redirect to the upgrade page --}}
            <a 
                href="{{ route('profile.upgrade') }}" 
                class="w-full text-center py-3 px-4 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition"
            >
                Upgrade Now
            </a>
        </div>
    </div>
</div>