<!DOCTYPE html>
<html lang="en">
@include('dashboard_layouts.head')
<body class="bg-gray-50 font-sans antialiased">
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transform transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-0"
            :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">
            @if(Auth::user()->hasRole('Responsable de projet') || Auth::user()->hasRole('Employee') || Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                @include('employees.sidebar')
            @endif
            @if(Auth::user()->hasRole('Admin'))
                @include('admin.sidebar')
            @endif
        </aside>

        <!-- Main ContentWrapper -->
        <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden">
            <!-- Header -->
            <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center gap-3">
                        <button @click="sidebarOpen = true" class="text-gray-500 focus:outline-none lg:hidden">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        </button>
                        <h1 class="text-xl font-bold text-gray-800">Customer Report Preview</h1>
                    </div>
                    <div class="flex items-center gap-4">
                        @include('dashboard_layouts.navbar')
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="w-full grow p-6">
                <div class="flex flex-col lg:flex-row gap-6 h-full">
                    <!-- Left Column: PDF Preview -->
                    <div class="w-full lg:w-2/3 flex flex-col">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden flex flex-col h-full min-h-[600px]">
                            <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                                <h4 class="text-lg font-semibold text-gray-800">PDF Preview</h4>
                            </div>
                            <div class="flex-1 bg-gray-100 p-0 relative">
                                <iframe src="{{ $pdfUrl }}" class="w-full h-full absolute inset-0 border-0"></iframe>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Email Options -->
                    <div class="w-full lg:w-1/3">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden sticky top-6">
                            <div class="p-4 border-b border-gray-200 bg-gray-50">
                                <h4 class="text-lg font-semibold text-gray-800">Email Options</h4>
                            </div>
                            <div class="p-6">
                                @if(session('success'))
                                    <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-700 flex items-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        {{ session('success') }}
                                    </div>
                                @endif

                                <form action="{{ route('admin.CustomerPointagerepports.send') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="start_date" value="{{ $startDate }}">
                                    <input type="hidden" name="end_date" value="{{ $endDate }}">
                                    <input type="hidden" name="project_id" value="{{ $projectId }}">
                                    <input type="hidden" name="employee_id" value="{{ $employeeId }}">
                                    <input type="hidden" name="task_ids" value="{{ json_encode($taskIds) }}">
                                    <input type="hidden" name="client_id" value="{{ $clientId }}">
                                    <input type="hidden" name="redirect_to" value="{{ route('admin.index') }}">

                                    <div class="mb-4">
                                        <label for="recipients" class="block text-sm font-medium text-gray-700 mb-1">Recipients (comma-separated)</label>
                                        <textarea id="recipients" name="recipients" rows="3" required
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2.5"
                                            placeholder="email@example.com, another@example.com">{{ implode(',', $projectContacts) }}</textarea>
                                    </div>

                                    <div class="mb-4">
                                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                        <input type="text" id="subject" name="subject" required
                                            value="Worksheet - {{ $projectName }} - {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2.5">
                                    </div>

                                    <div class="mb-6">
                                        <label for="body" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                                        <textarea id="body" name="body" rows="8" required
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2.5">Bonjour,
Je vous transmets ci-joint le worksheet pour validation.

N'hésitez pas à me contacter si vous avez besoin de précisions ou de documents complémentaires.
Merci d'avance pour votre retour.

Bien à vous</textarea>
                                    </div>

                                    <div class="flex flex-col sm:flex-row gap-3 pt-2">
                                        <button type="submit" class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 w-full sm:w-auto transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                            Send Email
                                        </button>
                                        <a href="{{ route('admin.tasks.pointage') }}" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 w-full sm:w-auto transition-colors">
                                            Back to Tasks
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        
        <!-- Mobile Sidebar Overlay -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"></div>
    </div>

    @include('dashboard_layouts.scripts')
</body>
</html>
