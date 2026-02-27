<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Edit Project') }}</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="flex-1 p-8 bg-[#eaebef]">
            <div class="max-w-4xl mx-auto">
                
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-2xl font-bold text-slate-900 mb-1">{{ __('messages.Edit Project') }}</h2>
                    <p class="text-sm text-slate-500">{{ __('messages.Update project details') }}</p>

                    @if(session('message'))
                    <div class="my-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg relative" role="alert">
                        {{ session('message') }}
                        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" data-bs-dismiss="alert" aria-label="Close">X</button>
                    </div>
                    @endif

                    <div class="border-t border-gray-200 my-5"></div>

                    @if(Auth::user()->hasRole('Admin'))
                        <form method="POST" action="{{ route('admin.projects.updateInfos', ['id' => $project->id, 'client_id' => $project->client_id]) }}" autocomplete="off" class="space-y-6">
                    @elseif(Auth::user()->hasRole('Responsable de projet'))
                        <form method="POST" action="{{ route('Responsable_de_projet.projects.updateInfos', ['id' => $project->id, 'client_id' => $project->client_id]) }}" autocomplete="off" class="space-y-6">
                    @elseif(Auth::user()->hasRole('Employee'))
                        <form method="POST" action="{{ route('employee.projects.updateInfos', ['id' => $project->id, 'client_id' => $project->client_id]) }}" autocomplete="off" class="space-y-6">
                    @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        <form method="POST" action="{{ route('Responsable_gestionnaire.projects.updateInfos', ['id' => $project->id, 'client_id' => $project->client_id]) }}" autocomplete="off" class="space-y-6">
                    @endif
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                            <div class="md:col-span-4">
                                <label for="projectName" class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Project Name') }} <span class="text-red-500">*</span></label>
                                <input type="text" id="projectName" name='name' value="{{ $project->name }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div class="md:col-span-2">
                                <label for="projectBudget" class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Budget label') }}</label>
                                <input type="number" step="0.0001" id="projectBudget" name='budget' value="{{ $project->budget }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div class="md:col-span-2">
                                <label for="costPerHour" class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.CPH') }} <span class="text-red-500">*</span></label>
                                <input type="number" step="0.0001" id="costPerHour" name='cost_per_hour' value="{{ $project->cost_per_hour }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div class="md:col-span-2">
                                <label for="estimatedHours" class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Est. Hours') }}</label>
                                <input type="number" step="0.0001" id="estimatedHours" name='estimated_hours' value="{{ $project->estimated_hours }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                            <div class="md:col-span-2">
                                <label for="tva" class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.TVA %') }} <span class="text-red-500">*</span></label>
                                <input type="number" step="0.0001" id="tva" name='tva' value="{{ $project->tva }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                            </div>
                        </div>

                        <div class="flex items-center">
                            <input id="regie" name="regie" type="checkbox" value="1" {{ $project->regie ? 'checked' : '' }} class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <label for="regie" class="ml-2 block text-sm text-gray-900">
                                {{ __('messages.Regie') }}
                            </label>
                        </div>

                        <div>
                            <label for="projectDescription" class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Informations') }}</label>
                            <textarea id="projectDescription" name="description" rows="4" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ $project->description }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('messages.Teams label') }}</label>
                                <div class="h-48 space-y-2 overflow-y-auto rounded-lg border border-gray-200 p-4">
                                    @foreach($teams as $team)
                                    <div class="flex items-center">
                                        @php 
                                            $project_teams = App\Models\project_teams::where('project_id', $project->id)
                                                ->where('team_id', $team->id)
                                                ->first();
                                        @endphp
                                        <input id="team{{$team->id}}" name="teams[]" type="checkbox" value="{{$team->id}}"
                                               {{ $project_teams ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <label for="team{{$team->id}}" class="ml-2 block text-sm text-gray-900">
                                            {{$team->name}}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('messages.Contacts label') }} <span class="text-red-500">*</span></label>
                                @php 
                                $client_contacts = App\Models\client_contacts::where('client_id', $project->client_id)
                                    ->whereNotNull('name')
                                    ->get();
                                @endphp
                                <div id="contactsDiv" class="h-48 space-y-2 overflow-y-auto rounded-lg border border-gray-200 p-4">
                                    @foreach($client_contacts as $contact)
                                    <div class="flex items-center">
                                        @php 
                                            $project_contacts = App\Models\client_contacts_projects::where('project_id', $project->id)
                                                ->where('contact_id', $contact->id)
                                                ->first();
                                        @endphp
                                        <input id="contact{{ $contact->id }}" name="contacts[]" type="checkbox" value="{{ $contact->id }}"
                                               {{ $project_contacts ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                        <label for="contact{{ $contact->id }}" class="ml-2 block text-sm text-gray-900">
                                            {{ $contact->name }}
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4 pt-4">
                            
                            <a href="javascript:history.back()" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg font-medium">
                                {{ __('messages.Cancel') }}
                            </a>
                            <button type="submit" name="button" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold shadow">
                                {{ __('messages.Update Project button') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    @include('dashboard_layouts.scripts')

    <script>
        // Disable autocomplete
        window.addEventListener('load', function() {
            var inputs = document.getElementsByTagName('input');
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].setAttribute('autocomplete', 'none');
            }
        });

        // Handle regie checkbox and project name
        document.getElementById('regie').addEventListener('change', function() {
            const projectNameInput = document.getElementById('projectName');
            const currentName = projectNameInput.value;
            
            if (this.checked) {
                // Add - REGIE if not already present
                if (!currentName.includes(' - REGIE')) {
                    projectNameInput.value = currentName + ' - REGIE';
                }
            } else {
                // Remove - REGIE if present
                projectNameInput.value = currentName.replace(' - REGIE', '');
            }
        });
    </script>
</body>
</html>
