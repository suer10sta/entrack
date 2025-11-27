<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> {{-- IMPORTANT pour AJAX --}}
    <title>DailyTrac Dashboard</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    <x-dashboard.sidebar />

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">

<div class="w-full max-w-7xl mx-auto"> {{-- Utilisation de max-w-7xl pour une page large --}}

    {{-- Message d'alerte (style Tailwind) --}}
    @if(session('alertmessage'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Erreur !</strong>
        <span class="block sm:inline">{{ session('alertmessage') }}</span>
        <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="Close">&times;</button>
    </div>
    @endif

    {{-- Carte de la Table (style Tailwind) --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tâche</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employé</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Début</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Fin</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Durée</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Déplacer Durée</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    {{-- LOGIQUE PHP D'ORIGINE RESTAURÉE --}}
                    @php 
                        $total_duration = 0;
                        $grouped_tasks = $tasks->groupBy(function($task) {
                            return \Carbon\Carbon::parse($task->start_date)->format('Y-m-d');
                        })->sortKeys();
                    @endphp
                    
                    @foreach($grouped_tasks as $date => $day_tasks)
                        @php
                            $daily_duration = 0;
                            $formatted_date = \Carbon\Carbon::parse($date)->format('d-m-Y');
                        @endphp
                        
                        {{-- Ligne d'en-tête de Date --}}
                        <tr>
                            <td colspan="6" class="px-6 py-3 bg-gray-100">
                                <h6 class="mb-0 text-sm font-bold text-gray-900">{{ \Carbon\Carbon::parse($date)->format('l') }} : {{ $formatted_date }}</h6>
                            </td>
                        </tr>
                        
                        @foreach($day_tasks as $task)
                            <tr>
                                {{-- Tâche --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    {{$task->task_name}}
                                </td>
                                {{-- Employé (LOGIQUE D'ORIGINE RESTAURÉE) --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @php
                                        $employee_name = App\Models\Employees::where('id', $task->employee_id)->pluck('name')->first();
                                    @endphp
                                    {{$employee_name}}
                                </td>
                                {{-- Date Début --}}
                                <td class="clickable px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium cursor-pointer hover:underline" data-field="start_date" data-task-id="{{ $task->id }}">
                                    {{ \Carbon\Carbon::parse($task->start_date)->format('d-m-Y H:i:s') }}
                                </td>
                                {{-- Date Fin --}}
                                <td class="clickable px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-medium cursor-pointer hover:underline" data-field="end_date" data-task-id="{{ $task->id }}">
                                    {{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->format('d-m-Y H:i:s') : 'En cours' }}
                                </td>
                                {{-- Durée (LOGIQUE D'ORIGINE RESTAURÉE) --}}
                                <td id="duration-{{ $task->id }}" class="duration clickable px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-bold cursor-pointer hover:underline" data-task-id="{{ $task->id }}">
                                    @php
                                        $tasktotalduration = $task->duration;
                                        $taskhours = floor($tasktotalduration / 60);
                                        $taskminutes = $tasktotalduration % 60;
                                        $total_duration += $tasktotalduration;
                                        $daily_duration += $tasktotalduration;
                                    @endphp
                                    {{$taskhours}}h:{{ str_pad($taskminutes, 2, '0', STR_PAD_LEFT) }}m
                                </td>
                                {{-- Déplacer Durée --}}
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button class="move-duration-btn inline-flex items-center bg-gray-600 hover:bg-gray-700 text-white font-medium px-3 py-1.5 rounded-lg text-xs" 
                                            data-task-id="{{$task->id}}" 
                                            data-project-id="{{$task->project_id}}" 
                                            data-employee-id="{{$task->employee_id}}"
                                            data-start-date="{{$task->start_date}}"
                                            data-end-date="{{$task->end_date}}"
                                            data-duration="{{$task->duration}}">
                                        Déplacer
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        
                        {{-- Ligne Total Quotidien (LOGIQUE D'ORIGINE RESTAURÉE) --}}
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-right text-sm font-bold text-gray-800">
                                Total Quotidien
                            </td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                @php
                                    $dailyhours = floor($daily_duration / 60);
                                    $dailyminutes = $daily_duration % 60;
                                @endphp
                                {{$dailyhours}}h:{{ str_pad($dailyminutes, 2, '0', STR_PAD_LEFT) }}m
                            </td>
                            <td class="px-6 py-4"></td> {{-- Cellule vide pour l'alignement --}}
                        </tr>
                    @endforeach
                    
                    {{-- Ligne Grand Total (LOGIQUE D'ORIGINE RESTAURÉE) --}}
                    <tr class="bg-gray-50 border-t-2 border-gray-300">
                        <td colspan="4" class="px-6 py-4 text-right text-base font-bold text-gray-900">
                            Grand Total
                        </td>
                        <td class="px-6 py-4 text-base font-bold text-blue-700">
                            @php
                                $totalhours = floor($total_duration / 60);
                                $totalminutes = $total_duration % 60;
                            @endphp
                            {{$totalhours}}h:{{ str_pad($totalminutes, 2, '0', STR_PAD_LEFT) }}m
                        </td>
                        <td class="px-6 py-4"></td> {{-- Cellule vide pour l'alignement --}}
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</main>
</div>

{{-- ====================================================== --}}
{{-- MODALS (STYLE TAILWIND)                              --}}
{{-- ====================================================== --}}

<div id="editDateModal" 
     class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900 bg-opacity-50 transition-opacity"
     aria-labelledby="editDateModalLabel" 
     role="dialog" 
     aria-modal="true">
    <div class="relative w-full max-w-md p-4 mx-auto bg-white rounded-lg shadow-xl transform transition-all sm:p-6">
        <form id="editDateForm">
            {{-- En-tête --}}
            <div class="flex items-start justify-between pb-4 border-b border-gray-200">
                <h5 class="text-lg font-semibold text-gray-900" id="editDateModalLabel">Modifier Date et Heure</h5>
                <button type="button" data-modal-close="editDateModal" class="text-gray-400 bg-transparent rounded-lg hover:bg-gray-200 hover:text-gray-900 text-sm p-1.5 ml-auto inline-flex items-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
            </div>
            {{-- Corps (Champs Date et Heure séparés, comme l'original) --}}
            <div class="py-6 space-y-4">
                <div>
                    <label for="dateInput" class="block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" id="dateInput" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="timeInput" class="block text-sm font-medium text-gray-700">Heure</label>
                    <input type="time" id="timeInput" required 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
                <input type="hidden" id="taskId" value="">
                <input type="hidden" id="fieldName" value="">
            </div>
            {{-- Pied de page --}}
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 pt-4 border-t border-gray-200">
                <button type="button" data-modal-close="editDateModal" class="mt-2 sm:mt-0 w-full sm:w-auto inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                    Fermer
                </button>
                <button type="button" id="saveChangesBtn" class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

<div id="moveDurationModal" 
     class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900 bg-opacity-50 transition-opacity"
     aria-labelledby="moveDurationModalLabel" 
     role="dialog" 
     aria-modal="true">
    <div class="relative w-full max-w-lg p-4 mx-auto bg-white rounded-lg shadow-xl transform transition-all sm:p-6">
        <form id="moveDurationForm">
            {{-- En-tête --}}
            <div class="flex items-start justify-between pb-4 border-b border-gray-200">
                <h5 class="text-lg font-semibold text-gray-900" id="moveDurationModalLabel">Déplacer la Durée</h5>
                <button type="button" data-modal-close="moveDurationModal" class="text-gray-400 bg-transparent rounded-lg hover:bg-gray-200 hover:text-gray-900 text-sm p-1.5 ml-auto inline-flex items-center">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
            </div>
            {{-- Corps --}}
            <div class="py-6 space-y-4">
                <div>
                    <label for="projectSelect" class="block text-sm font-medium text-gray-700">Projet</label>
                    <select id="projectSelect" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="taskSelect" class="block text-sm font-medium text-gray-700">Tâche</label>
                    <select id="taskSelect" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></select>
                </div>
                <div>
                    <label for="employeeSelect" class="block text-sm font-medium text-gray-700">Employé</label>
                    <select id="employeeSelect" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="moveStartDate" class="block text-sm font-medium text-gray-700">Date de Début</label>
                    <input type="datetime-local" id="moveStartDate" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
                <div>
                    <label for="moveEndDate" class="block text-sm font-medium text-gray-700">Date de Fin</label>
                    <input type="datetime-local" id="moveEndDate" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <p id="moveEndDateError" class="mt-2 text-sm text-red-600 hidden">La date de fin doit être après la date de début.</p>
                </div>
                <div>
                    <label for="moveDuration" class="block text-sm font-medium text-gray-700">Durée (HH:MM)</label>
                    <input type="text" id="moveDuration" required pattern="^([0-9]{2}h:[0-9]{2}m)$" placeholder="00h:00m" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    <p id="moveDurationError" class="mt-2 text-sm text-red-600 hidden">Veuillez entrer la durée au format : 00h:00m</p>
                </div>
                <input type="hidden" id="moveDurationMinutes">
                <input type="hidden" id="originalTaskId" value="">
            </div>
            {{-- Pied de page --}}
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 pt-4 border-t border-gray-200">
                <button type="button" data-modal-close="moveDurationModal" class="mt-2 sm:mt-0 w-full sm:w-auto inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                    Fermer
                </button>
                <button type="button" id="saveMoveChangesBtn" class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 1. jQuery (requis pour AJAX) --}}
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

{{-- 2. Bootstrap JS (PAS NÉCESSAIRE, remplacé par le JS de modal ci-dessous) --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> --}}

{{-- 3. Vos autres scripts (si @include('dashboard_layouts.scripts') les contient) --}}
{{-- @include('dashboard_layouts.scripts') --}}

{{-- 4. Scripts de logique de la page (LOGIQUE D'ORIGINE RESTAURÉE AVEC CONTRÔLE DE MODAL TAILWIND) --}}
<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- Fonctions de contrôle des Modals Tailwind ---
    const modals = {
        editDateModal: document.getElementById('editDateModal'),
        moveDurationModal: document.getElementById('moveDurationModal')
    };

    function openModal(modalId) {
        if (modals[modalId]) modals[modalId].classList.remove('hidden');
    }

    function closeModal(modalId) {
        if (modals[modalId]) modals[modalId].classList.add('hidden');
    }

    // Ajouter des écouteurs pour tous les boutons de fermeture
    document.querySelectorAll('[data-modal-close]').forEach(button => {
        button.addEventListener('click', () => {
            const modalId = button.getAttribute('data-modal-close');
            closeModal(modalId);
        });
    });
    
    // Fermer en cliquant sur le fond grisé
    Object.values(modals).forEach(modal => {
        if (modal) {
            modal.addEventListener('click', e => {
                if (e.target === modal) closeModal(modal.id);
            });
        }
    });
    // --- Fin des fonctions de contrôle des Modals ---


    // Gérer les clics sur les cellules date/heure
    const cells = document.querySelectorAll('.clickable');
    cells.forEach(cell => {
        cell.addEventListener('click', function() {
            const field = this.getAttribute('data-field');
            const taskId = this.getAttribute('data-task-id');
            const currentValue = this.textContent.trim();
            
            // Vérifier si c'est une cellule de durée
            if (this.classList.contains('duration')) {
                var newValue = prompt("Entrer la nouvelle durée pour la tâche (ex: 1h:30m ou 90m)");
                if (newValue) {
                    var updatedurl;
                    @if(Auth::user()->hasRole('Admin'))
                        updatedurl = '/admin/tasks/update-duration/' + taskId;
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet'))
                        updatedurl = '/Responsable_de_projet/tasks/update-duration/' + taskId;
                    @endif
                    @if(Auth::user()->hasRole('Employee'))
                        updatedurl = '/employee/tasks/update-duration/' + taskId;
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        updatedurl = '/Responsable_gestionnaire/tasks/update-duration/' + taskId;
                    @endif

                    $.ajax({
                        url: updatedurl,
                        type: 'POST',
                        data: {
                            duration: newValue,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.status == 'success') {
                                location.reload();
                            } else {
                                location.reload(); 
                            }
                        }
                    });
                }
            } else {
                // Gérer l'édition de date/heure
                if (currentValue !== 'En cours' && currentValue) {
                    const [datePart, timePart] = currentValue.split(' ');
                    const [day, month, year] = datePart.split('-');
                    const formattedDate = `${year}-${month}-${day}`;
                    const formattedTime = timePart ? timePart.substring(0, 5) : '00:00';

                    document.getElementById('dateInput').value = formattedDate;
                    document.getElementById('timeInput').value = formattedTime;
                    document.getElementById('taskId').value = taskId;
                    document.getElementById('fieldName').value = field;

                    openModal('editDateModal'); // Appel modal
                }
            }
        });
    });

    // Gérer le bouton "Sauvegarder" dans le modal de date
    document.getElementById('saveChangesBtn').addEventListener('click', function() {
        const taskId = document.getElementById('taskId').value;
        const field = document.getElementById('fieldName').value;
        const date = document.getElementById('dateInput').value;
        const time = document.getElementById('timeInput').value;
        const newValue = `${date} ${time}`;

        var updateUrl;
        @if(Auth::user()->hasRole('Admin'))
            updateUrl = `/admin/tasks/update-${field}/${taskId}`;
        @endif
        @if(Auth::user()->hasRole('Responsable de projet'))
            updateUrl = `/Responsable_de_projet/tasks/update-${field}/${taskId}`;
        @endif
        @if(Auth::user()->hasRole('Employee'))
            updateUrl = `/employee/tasks/update-${field}/${taskId}`;
        @endif
        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            updateUrl = `/Responsable_gestionnaire/tasks/update-${field}/${taskId}`;
        @endif

        $.ajax({
            url: updateUrl,
            type: 'POST',
            data: {
                [field]: newValue,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.status === 'success') {
                    location.reload();
                } else {
                    alert('Échec de la mise à jour.');
                }
            }
        });
        closeModal('editDateModal');
    });

    // ==========================================================
    // DEBUT DU CODE DU SECOND SCRIPT (MAINTENANT FUSIONNÉ)
    // ==========================================================

    // Gérer les clics sur le bouton "Déplacer Durée"
    document.querySelectorAll('.move-duration-btn').forEach(button => {
        button.addEventListener('click', function() {
            const taskId = this.getAttribute('data-task-id');
            const projectId = this.getAttribute('data-project-id');
            const employeeId = this.getAttribute('data-employee-id');
            const startDate = this.getAttribute('data-start-date'); // (Assurez-vous que le HTML est corrigé en 'data-start_date')
            const endDate = this.getAttribute('data-end-date');
            const duration = this.getAttribute('data-duration');

            document.getElementById('projectSelect').value = projectId;
            document.getElementById('employeeSelect').value = employeeId;
            document.getElementById('moveStartDate').value = formatDateTimeForInput(startDate);
            document.getElementById('moveEndDate').value = formatDateTimeForInput(endDate);
            document.getElementById('moveDuration').value = minutesToHoursMinutes(parseInt(duration));
            document.getElementById('moveDurationMinutes').value = duration;
            document.getElementById('originalTaskId').value = taskId;

            loadTasksForProject(projectId, taskId);

            // Appel du modal Tailwind (fonctionnera maintenant)
            openModal('moveDurationModal');
        });
    });

    // Gérer le changement de sélection de projet
    document.getElementById('projectSelect').addEventListener('change', function() {
        loadTasksForProject(this.value);
    });

    // Formater la date/heure pour l'input
    function formatDateTimeForInput(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const userTimezoneOffset = date.getTimezoneOffset() * 60000;
        const localDate = new Date(date.getTime() - userTimezoneOffset);
        return localDate.toISOString().slice(0, 16);
    }

    // Charger les tâches pour un projet
    function loadTasksForProject(projectId, selectedTaskId = null) {
        let url;
        @if(Auth::user()->hasRole('Admin'))
            url = `/admin/tasks/get-project-tasks/${projectId}`;
        @endif
        @if(Auth::user()->hasRole('Responsable de projet'))
            url = `/Responsable_de_projet/tasks/get-project-tasks/${projectId}`;
        @endif
        @if(Auth::user()->hasRole('Employee'))
            url = `/employee/tasks/get-project-tasks/${projectId}`;
        @endif
        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            url = `/Responsable_gestionnaire/tasks/get-project-tasks/${projectId}`;
        @endif

        fetch(url)
            .then(response => response.json())
            .then(tasks => {
                const taskSelect = document.getElementById('taskSelect');
                taskSelect.innerHTML = '';
                
                tasks.forEach(task => {
                    const option = new Option(task.name, task.id);
                    if (selectedTaskId && task.id == selectedTaskId) {
                        option.selected = true;
                    }
                    taskSelect.add(option);
                });
            });
    }

    // Convertir les minutes en format HH:MM
    function minutesToHoursMinutes(minutes) {
        const hours = Math.floor(minutes / 60);
        const remainingMinutes = minutes % 60;
        return `${hours.toString().padStart(2, '0')}h:${remainingMinutes.toString().padStart(2, '0')}m`;
    }

    // Convertir le format HH:MM en minutes
    function hoursMinutesToMinutes(durationString) {
        const matches = durationString.match(/^(\d{2})h:(\d{2})m$/);
        if (!matches) return null;
        
        const hours = parseInt(matches[1]);
        const minutes = parseInt(matches[2]);
        return (hours * 60) + minutes;
    }

    // Gérer l'état d'erreur pour les inputs Tailwind
    function setInputError(inputId, errorId, message, isError) {
        const input = document.getElementById(inputId);
        const errorEl = document.getElementById(errorId);
        if (isError) {
            input.classList.add('border-red-500', 'focus:ring-red-500');
            input.classList.remove('border-gray-300', 'focus:ring-blue-500');
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
        } else {
            input.classList.remove('border-red-500', 'focus:ring-red-500');
            input.classList.add('border-gray-300', 'focus:ring-blue-500');
            errorEl.classList.add('hidden');
        }
    }

    // Auto-calculer la durée
    function calculateAndSetDuration() {
        const startValue = document.getElementById('moveStartDate')?.value;
        const endValue = document.getElementById('moveEndDate')?.value;
        const durationInput = document.getElementById('moveDuration');
        const durationMinutesInput = document.getElementById('moveDurationMinutes');

        if (!startValue || !endValue) return;

        const start = new Date(startValue);
        const end = new Date(endValue);
        const diffMs = end - start;

        if (Number.isNaN(diffMs) || diffMs < 0) {
            setInputError('moveEndDate', 'moveEndDateError', 'La date de fin doit être après la date de début.', true);
            durationInput.value = '00h:00m';
            durationMinutesInput.value = '0';
            return;
        } else {
            setInputError('moveEndDate', 'moveEndDateError', '', false);
        }

        const minutes = Math.floor(diffMs / 60000);
        durationInput.value = minutesToHoursMinutes(minutes);
        durationMinutesInput.value = String(minutes);
        setInputError('moveDuration', 'moveDurationError', '', false);
    }

    // Écouter les changements sur les inputs de date/heure
    const moveStartDateEl = document.getElementById('moveStartDate');
    const moveEndDateEl = document.getElementById('moveEndDate');
    if (moveStartDateEl) moveStartDateEl.addEventListener('change', calculateAndSetDuration);
    if (moveEndDateEl) moveEndDateEl.addEventListener('change', calculateAndSetDuration);

    // Valider le format de la durée
    document.getElementById('moveDuration').addEventListener('input', function(e) {
        const input = e.target;
        const minutes = hoursMinutesToMinutes(input.value);
        
        if (minutes !== null) {
            setInputError('moveDuration', 'moveDurationError', '', false);
            document.getElementById('moveDurationMinutes').value = minutes;
        } else {
            setInputError('moveDuration', 'moveDurationError', 'Veuillez entrer la durée au format : 00h:00m', true);
        }
    });

    // Gérer le bouton "Sauvegarder" du modal "Déplacer Durée"
    document.getElementById('saveMoveChangesBtn').addEventListener('click', function() {
        const durationInput = document.getElementById('moveDuration');
        const durationMinutes = document.getElementById('moveDurationMinutes').value;

        if (!durationInput.value.match(/^[0-9]{2}h:[0-9]{2}m$/)) {
            alert('Veuillez entrer la durée au format correct (00h:00m)');
            setInputError('moveDuration', 'moveDurationError', 'Veuillez entrer la durée au format : 00h:00m', true);
            return;
        }
        
        if (document.getElementById('moveEndDate').classList.contains('border-red-500')) {
             alert('La date de fin ne peut pas être antérieure à la date de début.');
             return;
        }

        const formData = {
            original_task_id: document.getElementById('originalTaskId').value,
            new_task_id: document.getElementById('taskSelect').value,
            employee_id: document.getElementById('employeeSelect').value,
            start_date: document.getElementById('moveStartDate').value,
            end_date: document.getElementById('moveEndDate').value,
            duration: durationMinutes,
            _token: '{{ csrf_token() }}'
        };

        let url;
        @if(Auth::user()->hasRole('Admin'))
            url = '/admin/tasks/move-duration';
        @endif
        @if(Auth::user()->hasRole('Responsable de projet'))
            url = '/Responsable_de_projet/tasks/move-duration';
        @endif
        @if(Auth::user()->hasRole('Employee'))
            url = '/employee/tasks/move-duration';
        @endif
        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            url = '/Responsable_gestionnaire/tasks/move-duration';
        @endif

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.status === 'success') {
                    location.reload();
                } else {
                    alert('Échec du déplacement de la durée : ' + (response.message || 'Erreur inconnue'));
                }
            },
            error: function(xhr, status, error) {
                console.error('Détails de l\'erreur:', { status, error, response: xhr.responseText });
                alert('Erreur lors du déplacement de la durée : ' + error);
            }
        });
        
        closeModal('moveDurationModal');
    });

});
</script>
</body>
</html>