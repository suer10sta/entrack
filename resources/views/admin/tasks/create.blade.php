<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DailyTrac Dashboard - New Task</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    <x-dashboard.sidebar />

    {{-- Main content area --}}
    <div class="flex flex-col flex-1 overflow-y-auto">
        
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Page Content --}}
        <main class="p-6 md:p-8 lg:p-10">
            
            {{-- This block determines the correct routes ONCE, avoiding huge repetition --}}
            @php
                $user = Auth::user();
                $actionRoute = '#'; // Default fallback
                $cancelRoute = '#'; // Default fallback

                if ($user->hasRole('Admin')) {
                    $actionRoute = route('admin.tasks.store', $project->id);
                    $cancelRoute = route('admin.index');
                } elseif ($user->hasRole('Responsable de projet')) {
                    $actionRoute = route('Responsable_de_projet.tasks.store', $project->id);
                    $cancelRoute = route('Responsable_de_projet.index');
                } elseif ($user->hasRole('Employee')) {
                    $actionRoute = route('employee.tasks.store', $project->id);
                    $cancelRoute = route('employee.index');
                } elseif ($user->hasRole('Responsable de projet et gestionnaire')) {
                    $actionRoute = route('Responsable_gestionnaire.tasks.store', $project->id);
                    $cancelRoute = route('Responsable_gestionnaire.index');
                }
            @endphp

            <div class="max-w-4xl mx-auto">
                {{-- Replaced .card.card-body with Tailwind classes --}}
                <div class="bg-white shadow-lg rounded-xl p-6 md:p-8">
                    
                    <h2 class="text-xl font-semibold text-gray-900">New Task</h2>
                    <p class="text-sm text-gray-600">Create a new task for the project: **{{ $project->name }}**</p>
                    
                    <hr class="my-4 border-gray-200" />

                    {{-- Single form tag using the $actionRoute variable --}}
                    <form method="POST" action="{{ $actionRoute }}" autocomplete="off">
                        @csrf
                        
                        {{-- Form fields in a responsive grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="TaskName" class="block text-sm font-medium text-gray-700">
                                    Task Name <span class="text-red-600">*</span>
                                </label>
                                <input type="text" id="TaskName" name="title"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>
                            
                            <div>
                                <label for="estimated_hours" class="block text-sm font-medium text-gray-700">
                                    Estimated Hours
                                </label>
                                <input type="text" id="estimated_hours" name="estimated_hours"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="TaskDescription" class="block text-sm font-medium text-gray-700">
                                Task Description
                            </label>
                            <textarea id="TaskDescription" name="description" rows="4"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        </div>

                        {{-- Checkbox sections in a responsive grid --}}
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Employees Checkboxes --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Assign Employees <span class="text-red-600">*</span>
                                </label>
                                <div class="mt-2 space-y-2">
                                    @foreach($employee_users as $employee)
                                        <div class="flex items-center">
                                            <input id="employee{{$employee->id}}" name="employees[]" type="checkbox" value="{{$employee->id}}"
                                                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <label for="employee{{$employee->id}}" class="ml-3 block text-sm text-gray-800">
                                                {{$employee->name}}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            {{-- Teams Checkboxes --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Assign Teams
                                </label>
                                <div class="mt-2 space-y-2">
                                    @foreach($teams as $team)
                                        <div class="flex items-center">
                                            <input id="team{{$team->id}}" name="teams[]" type="checkbox" value="{{$team->id}}"
                                                   class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                            <label for="team{{$team->id}}" class="ml-3 block text-sm text-gray-800">
                                                {{$team->name}}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Form Action Buttons --}}
                        <div class="flex justify-end items-center gap-x-4 mt-8 pt-4 border-t border-gray-200">
                            {{-- Single "Cancel" link --}}
                            <a href="javascript:history.back()" 
                               class="rounded-md bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                Cancel
                            </a>
                            
                            <button type="submit"
                                    class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                Create Task
                            </button>
                        </div>
                        
                    </form>
                </div>
            </div>
        </main>
    </div>

    
    <script>
    // This script is redundant if you set autocomplete="off" on the form tag, but left it just in case.
    window.addEventListener('load', function() {
        var inputs = document.getElementsByTagName('input');
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].setAttribute('autocomplete', 'none');
        }
    });
    </script>

    <script>
    // This script was in your original file. It seems to be for a dropdown that is not in this form.
    // It might be part of your navbar component.
    document.addEventListener('DOMContentLoaded', function () {
        const dropdownButton = document.getElementById('taskDropdown');
        const selectedProjectInput = document.getElementById('selectedProject');

        if (dropdownButton && selectedProjectInput) {
            document.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', function (event) {
                    event.preventDefault();
                    const selectedProject = event.target.getAttribute('data-value');
                    dropdownButton.textContent = selectedProject;
                    selectedProjectInput.value = selectedProject;
                });
            });
        }
    });
    </script>

</body>
</html>
