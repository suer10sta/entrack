<?php

namespace App\Livewire;
use Livewire\Component;
use Carbon\Carbon;
use App\Models\calendars;
use App\Models\User; 

class FullCalendarModal extends Component
{
    public $showModal = false;
    public $currentDate; 
    public $selectedDate; 
    public $events = [];

    public $showEventForm = false;
    public $eventTitle = '';
    public $eventStartTime = '';
    public $eventEndTime = '';

    public $eventDescription = '';
    public $eventColor = '#3B82F6'; 

    protected $listeners = ['openCalendarModal' => 'open',
                            'openCalendarForm' => 'openAndShowForm'
                        ];

    public function mount()
    {
        $this->currentDate = Carbon::now();
        $this->selectedDate = Carbon::now();
        $this->loadEvents();
    }

    public function open()
    {
        $this->currentDate = Carbon::now();
        $this->selectedDate = Carbon::now();
        $this->loadEvents();
        $this->showEventForm = false;
        $this->showModal = true;
    }

    public function close()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function openAndShowForm()
    {
        $this->open(); 
        $this->showEventForm = true; 
    }

    public function loadEvents()
    {
        $entreprise_id = auth()->user()->entreprise_id;

        // Show all events in the same enterprise
        $this->events = calendars::where('entreprise_id', $entreprise_id)
            ->whereDate('start_date', $this->selectedDate->toDateString())
            ->with('creator.employee')
            ->orderBy('start_date')
            ->get();
    }

    public function selectDate($day)
    {
        $this->selectedDate = $this->currentDate->copy()->setDay($day);
        $this->loadEvents();
    }

    public function goToNextMonth()
    {
        $this->currentDate->addMonth();
    }

    public function goToPreviousMonth()
    {
        $this->currentDate->subMonth();
    }

    public function showAddForm()
    {
        $this->showEventForm = true;
    }

    public function closeAddForm()
    {
        $this->resetForm();
        $this->showEventForm = false;
    }

    public function resetForm()
    {
        $this->reset(['eventTitle', 'eventStartTime', 'eventEndTime', 'eventDescription', 'eventColor']);
        $this->eventColor = '#3B82F6';
    }

    public function saveEvent()
    {
        $this->validate([
            'eventTitle' => 'required|string|min:3',
            'eventDescription' => 'nullable|string',
            'eventColor' => 'required|string',
            'eventStartTime' => 'required|date',
            'eventEndTime' => 'required|date|after:eventStartTime',
        ]);

        $startDateTime = $this->selectedDate->copy()->setTimeFromTimeString($this->eventStartTime);
        $endDateTime = $this->selectedDate->copy()->setTimeFromTimeString($this->eventEndTime);

        calendars::create([
            'entreprise_id' => auth()->user()->entreprise_id,
            'user_id' => auth()->id(), 
            'title' => $this->eventTitle,
            'description' => $this->eventDescription, 
            'color' => $this->eventColor,
            'start_date' => $startDateTime,
            'end_date' => $endDateTime,
        ]);

        $this->closeAddForm();
        $this->loadEvents();
    }

    public function render()
    {
        $firstDayOfMonth = $this->currentDate->copy()->firstOfMonth()->dayOfWeek;
        $daysInMonth = $this->currentDate->daysInMonth;

        return view('livewire.full-calendar-modal', [
            'firstDayOfMonth' => $firstDayOfMonth,
            'daysInMonth' => $daysInMonth,
        ]);
    }
    
}
