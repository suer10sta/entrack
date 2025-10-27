{{-- resources/views/livewire/full-calendar-modal.blade.php --}}
<div>
    @if ($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4" wire:click.self="close">

        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">

            {{-- SWITCH BETWEEN FORM AND CALENDAR VIEW --}}
            @if ($showEventForm)

                {{-- ################################# --}}
                {{-- NEW "ADD EVENT" FORM DESIGN --}}
                {{-- ################################# --}}

                <div class="flex items-center gap-3 p-6 border-b">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 flex-1">Add New Event</h2>
                    {{-- Close Button (replaces Back) --}}
                    <button wire:click="closeAddForm" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveEvent">
                    <div class="p-6 space-y-5">
                        {{-- Event Title --}}
                        <div>
                            <label for="eventTitle" class="block text-sm font-medium text-gray-700 mb-1">Event Title <span class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="eventTitle" id="eventTitle" placeholder="Enter event title" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('eventTitle') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="eventDescription" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea wire:model.defer="eventDescription" id="eventDescription" placeholder="Enter event description" rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                            @error('eventDescription') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Color Picker --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                            <div class="flex space-x-2">
                                @php $colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899']; @endphp
                                @foreach ($colors as $color)
                                <label class="cursor-pointer">
                                    <input type="radio" wire:model="eventColor" value="{{ $color }}" class="sr-only">
                                    <span class="w-7 h-7 rounded-full inline-block border-2 {{ $eventColor == $color ? 'border-gray-700 ring-2 ring-offset-1 ring-gray-500' : 'border-transparent' }}" style="background-color: {{ $color }}"></span>
                                </label>
                                @endforeach
                            </div>
                             @error('eventColor') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Start Date & Time --}}
                        <div>
                            <label for="eventStartTime" class="block text-sm font-medium text-gray-700 mb-1">Start Date & Time <span class="text-red-500">*</span></label>
                            <input type="datetime-local" wire:model.defer="eventStartTime" id="eventStartTime" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('eventStartTime') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- End Date & Time --}}
                        <div>
                            <label for="eventEndTime" class="block text-sm font-medium text-gray-700 mb-1">End Date & Time <span class="text-red-500">*</span></label>
                            <input type="datetime-local" wire:model.defer="eventEndTime" id="eventEndTime" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @error('eventEndTime') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Form Footer (Buttons) --}}
                    <div class="bg-gray-50 p-6 rounded-b-2xl border-t flex justify-end gap-3">
                    <button type="button" wire:click.stop.prevent="closeAddForm" class="text-sm bg-white hover:bg-gray-50 border border-gray-300 text-gray-700 py-2 px-4 rounded-lg">                            Cancel
                        </button>
                        <button type="submit" class="text-sm bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg">
                            Add Event
                        </button>
                    </div>
                </form>

            @else

                {{-- ################################# --}}
                {{-- ORIGINAL CALENDAR VIEW --}}
                {{-- ################################# --}}

                <div class="flex items-center justify-between p-6 border-b">
                    <h2 class="text-xl font-semibold text-gray-900">Full Calendar</h2>
                    <button wire:click="close" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <button wire:click="goToPreviousMonth" class="p-1 rounded-full hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        <h3 class="text-lg font-semibold">{{ $currentDate->format('F Y') }}</h3>
                        <button wire:click="goToNextMonth" class="p-1 rounded-full hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-7 gap-2 text-center">
                        @foreach(['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as $day)
                            <div class="text-xs font-medium text-gray-500">{{ $day }}</div>
                        @endforeach
                        @for ($i = 0; $i < $firstDayOfMonth; $i++)
                            <div></div>
                        @endfor
                        @for ($day = 1; $day <= $daysInMonth; $day++)
                            <button
                                wire:click="selectDate({{ $day }})"
                                class="w-8 h-8 rounded-full flex items-center justify-center
                                    {{ $selectedDate->day == $day ? 'bg-blue-600 text-white' : 'hover:bg-blue-50' }}">
                                {{ $day }}
                            </button>
                        @endfor
                    </div>
                </div>

                <div class="bg-gray-50 p-6 rounded-b-2xl border-t">
                    <div class="space-y-3"> {{-- Keep space between event cards --}}
                        @forelse($events as $event)
                        {{-- NEW: Wrap each event in a styled card --}}
                        <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100"> 
                            <p class="font-semibold text-gray-800">{{ $event->title }}</p>
                            <p class="text-sm text-gray-500 mt-0.5"> {{-- Adjusted margin-top --}}
                                {{ \Carbon\Carbon::parse($event->start_date)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($event->end_date)->format('H:i') }}
                            </p>
                        </div>
                        
                        @empty
                        <p class="text-sm text-gray-500">No events for this day.</p>
                        @endforelse
                    </div>
                </div>

            @endif
            {{-- END OF @if/@else BLOCK --}}

        </div>
    </div>
    @endif
</div>