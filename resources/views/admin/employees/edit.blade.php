<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Assign Role') }} - DalyTrac</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <link rel="stylesheet" href="{{ asset('css/dalytrac-employee-roles.css') }}">
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden font-sans">
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif
    <div class="flex flex-col flex-1 min-w-0 overflow-y-auto">
        <x-dashboard.navbar />
        @php
            $prefix = 'admin';
            if (!Auth::user()->hasRole('Admin')) {
                if (Auth::user()->hasRole('Responsable de projet')) $prefix = 'Responsable_de_projet';
                elseif (Auth::user()->hasRole('Employee')) $prefix = 'employee';
                elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $prefix = 'Responsable_gestionnaire';
                elseif (Auth::user()->hasRole('SuperAdmin')) $prefix = 'SuperAdmin';
            }
            $roleCreateRoute = null;
            if (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('Responsable de projet') || Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                $roleCreateRoute = route($prefix.'.roles.create', ['assign_employee' => $employee->id, 'redirect_route' => request()->route()->getName()]);
            }
        @endphp
        <main class="employee-roles-edit">
            <div class="ere-container">
                <nav class="ere-breadcrumb" aria-label="{{ __('messages.Employees') }}"><a href="{{ route($prefix.'.employees.index') }}">{{ __('messages.Employees') }}</a><span aria-hidden="true">/</span><span>{{ __('messages.Assign Role') }}</span></nav>
                <header class="ere-header"><span class="ere-eyebrow">{{ __('messages.Employees') }}</span><h1>{{ __('messages.Assign Role') }}</h1><p>{{ __('messages.employee_roles_intro') }}</p></header>
                @if($errors->any())
                    <div class="ere-errors" role="alert"><ul>@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
                @endif
                <form method="POST" action="{{ route($prefix.'.employees.update', $employee) }}" class="ere-card">
                    @csrf
                    @method('PUT')
                    <div class="ere-person">
                        <span class="ere-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name ?? '', 0, 1)) }}</span>
                        <div class="ere-person-info"><strong>{{ $user->name }}</strong><span>{{ $user->email }}</span></div>
                        <svg class="ere-shield" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 8 3v6c0 4-4 7-8 9-4-2-8-5-8-9V6l8-3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m8 12 3 3 5-6"/></svg>
                    </div>
                    <section class="ere-roles" aria-labelledby="ere-roles-title">
                        <div class="ere-section-header">
                            <div><h2 id="ere-roles-title">{{ __('messages.Assign Roles') }}</h2><p id="ere-roles-help">{{ __('messages.employee_roles_help') }}</p></div>
                            @if($roleCreateRoute)<a href="{{ $roleCreateRoute }}" class="dt-btn dt-btn-ghost"><span aria-hidden="true">＋</span>{{ __('messages.Add New Role') }}</a>@endif
                        </div>
                        <div class="ere-role-grid" role="group" aria-labelledby="ere-roles-title" aria-describedby="ere-roles-help">
                            @foreach($role_name as $role)
                                @if(Auth::user()->hasRole('SuperAdmin') || (!$user->hasRole('SuperAdmin') && $role != 'SuperAdmin'))
                                    <label class="ere-role" for="employee-role-{{ $loop->index }}">
                                        <input id="employee-role-{{ $loop->index }}" name="roles[]" type="checkbox" value="{{ $role }}" @checked($user->hasRole($role))>
                                        <span>{{ $role }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </section>
                    <footer class="ere-footer">
                        <button type="button" onclick="window.history.back()" class="dt-btn dt-btn-ghost">{{ __('messages.Cancel') }}</button>
                        <button type="submit" class="dt-btn dt-btn-primary">{{ __('messages.Save') }}</button>
                    </footer>
                </form>
            </div>
        </main>
    </div>
    @livewireScripts
</body>
</html>
