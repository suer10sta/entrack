<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BizManager Dashboard</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    <x-dashboard.sidebar />
    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="flex-1 overflow-y-auto p-8 bg-gray-50">
            <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Assign Role</h2>

                {{-- Form --}}
                @if(Auth::user()->hasRole('Admin'))
                    <form method="POST" action="{{ route('admin.employees.update', $employee) }}">
                @elseif(Auth::user()->hasRole('Responsable de projet'))
                    <form method="POST" action="{{ route('Responsable_de_projet.employees.update', $employee) }}">
                @elseif(Auth::user()->hasRole('Employee'))
                    <form method="POST" action="{{ route('employee.employees.update', $employee) }}">
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    <form method="POST" action="{{ route('Responsable_gestionnaire.employees.update', $employee) }}">
                @endif
                    @csrf
                    @method('PUT')

                    {{-- Roles Section --}}
                    <div class="mb-8">
                        <h5 class="text-lg font-semibold text-gray-800 mb-3">Assign Roles</h5>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach($role_name as $role)
                                @if(Auth::user()->hasRole('SuperAdmin') || (!$user->hasRole('SuperAdmin') && $role != 'SuperAdmin'))
                                    <label class="flex items-center space-x-2 bg-gray-50 hover:bg-gray-100 rounded-lg px-3 py-2 border border-gray-200 cursor-pointer">
                                        <input id="vue-checkbox-{{ $role }}" name="roles[]" type="checkbox" value="{{ $role }}"
                                            class="text-blue-600 focus:ring-blue-500"
                                            @if($user->hasRole($role)) checked @endif>
                                        <span class="text-sm text-gray-700">{{ $role }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Timezone --}}
                    <div class="mb-8">
                        <h5 class="text-lg font-semibold text-gray-800 mb-3">Timezone Settings</h5>
                        <select name="timezone"     class="w-full md:w-1/3 rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-base py-2 px-4 shadow-sm bg-white">
                            @foreach (timezone_identifiers_list() as $timezone)
                                <option value="{{ $timezone }}" @if($user->timezone == $timezone) selected @endif>
                                    {{ $timezone }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex justify-between items-center mt-6">
                        @if(Auth::user()->hasRole('Responsable de projet'))
                            <a href="{{route('Responsable_de_projet.index')}}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 text-sm font-medium">Cancel</a>
                        @elseif(Auth::user()->hasRole('Admin'))
                            <a href="{{route('admin.index')}}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 text-sm font-medium">Cancel</a>
                        @elseif(Auth::user()->hasRole('Employee'))
                            <a href="{{route('employee.index')}}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 text-sm font-medium">Cancel</a>
                        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            <a href="{{route('Responsable_gestionnaire.index')}}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-gray-700 text-sm font-medium">Cancel</a>
                        @endif
                        <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium shadow-sm">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </main>

    </div>
    
</body>
</html>
