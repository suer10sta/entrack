<header class="flex items-center justify-between px-6 py-3 bg-white border-b">
    <div class="flex items-center w-full max-w-2xl">
        <div class="relative w-full">
            <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input type="text" placeholder="Search..." 
                class="w-full pl-10 pr-4 py-2 border rounded-lg bg-gray-50 focus:ring-0 focus:border-blue-300 text-sm" />
        </div>
    </div>

    <div class="flex items-center gap-4 ml-6">
        <button class="p-2 rounded-md hover:bg-gray-100">
            <i class="fa-solid fa-ellipsis-vertical text-gray-500"></i>
        </button>

        <button class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
            + New Invoice
        </button>
    </div>
</header>