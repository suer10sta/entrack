<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Roles Page Title') }}</title>
    @vite('resources/css/app.css')
    @livewireStyles

    <style>
        /* Mobile: Roles table as modern 2-col cards (no horizontal scroll) */
        @media (max-width: 1024px) {
            .roles-table-wrapper {
                overflow-x: visible !important;
            }

            #rolesTable thead {
                display: none;
            }

            #rolesTable tbody {
                display: block;
                width: 100%;
            }

            #rolesTable tbody tr {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 16px;
                padding: 12px;
                border: 1px solid rgba(148, 163, 184, 0.55);
                border-radius: 16px;
                background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(248,250,252,0.92));
                box-shadow: 0 18px 45px rgba(15,23,42,0.10), 0 3px 10px rgba(15,23,42,0.08);
            }

            #rolesTable tbody td {
                display: block;
                width: 100%;
                padding: 10px 12px !important;
                border: 1px solid rgba(226, 232, 240, 0.9);
                border-radius: 12px;
                background: rgba(255,255,255,0.78);
                text-align: left !important;
                white-space: normal !important;
                word-break: break-word;
            }

            #rolesTable tbody td[data-span="2"] {
                grid-column: span 2 / span 2;
            }

            #rolesTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: #64748b;
                margin-bottom: 6px;
            }

            #rolesTable tbody td[data-label="{{ __('messages.ACTIONS header') }}"] .flex {
                justify-content: center;
            }
        }
    </style>
</head>

<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">

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
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] p-4 sm:p-6 lg:px-20 lg:py-8">

            <div class="max-w-7xl mx-auto w-full">

                {{-- Header --}}
                <div class="mb-5">
                    <p class="dt-overline mb-1">{{ __('messages.System') }}</p>
                    <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">{{ __('messages.Roles Management') }}</h1>
                    <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.Roles Management subtitle') }}</p>
                </div>

                {{-- Controls --}}
                <div class="dt-card p-4 sm:p-5 mb-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">

                        {{-- Entries per page (placeholder UI, optional Livewire binding) --}}
                        <div class="flex items-center gap-2">
                            <label class="text-[13px] font-medium text-slate-700">{{ __('messages.Entries per page') }}</label>
                            <select
                                class="h-10 px-3 rounded-xl border border-slate-200 bg-white text-[13px] text-slate-700 outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 w-full sm:w-auto cursor-pointer">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>

                        {{-- Add Role
                        <a href="{{ route('admin.roles.create') }}"
                           class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Role
                        </a>
                        --}}
                    </div>

                    {{-- Search Bar --}}
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none"
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18.5a7.5 7.5 0 006.15-3.85z"/>
                        </svg>
                        <input
                            id="searchInput"
                            type="text"
                            placeholder="{{ __('messages.Search roles placeholder') }}"
                            class="w-full h-10 pl-9 pr-4 rounded-xl border border-slate-200 bg-white text-[13px] text-slate-700 placeholder:text-slate-400 shadow-sm outline-none transition focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25"
                            wire:model.live="search"
                        />
                    </div>
                </div>

                {{-- Table --}}
                <div class="dt-card overflow-hidden">
                    <div class="roles-table-wrapper overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse min-w-full" id="rolesTable">
                            <thead class="border-b border-slate-100 bg-slate-50/80">
                                <tr class="dt-table-head">
                                    <th class="px-4 py-3 text-left whitespace-nowrap min-w-[200px]">{{ __('messages.ROLE header') }}</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap min-w-[220px]">{{ __('messages.PERMISSIONS header') }}</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap min-w-[130px]">{{ __('messages.ACTIONS header') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($roles as $role)
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        {{-- Role name --}}
                                        @php
                                            $roleRawName = trim($role->name);
                                            $roleCandidates = array_values(array_unique([
                                                $roleRawName,
                                                strtolower($roleRawName),
                                                strtoupper($roleRawName),
                                                str_replace(['_', '-'], ' ', $roleRawName),
                                                ucwords(strtolower(str_replace(['_', '-'], ' ', $roleRawName))),
                                                str_replace(' ', '_', $roleRawName),
                                                strtolower(str_replace(' ', '_', $roleRawName)),
                                                strtoupper(str_replace(' ', '_', $roleRawName)),
                                            ]));
                                            $roleLabel = $roleRawName;
                                            foreach ($roleCandidates as $candidate) {
                                                $candidateKey = 'messages.' . $candidate;
                                                $translated = __($candidateKey);
                                                if ($translated !== $candidateKey) {
                                                    $roleLabel = $translated;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        <td data-label="{{ __('messages.ROLE header') }}" data-span="2" class="px-4 py-3 text-[13px] font-semibold text-[#0B2638]">{{ $roleLabel }}</td>

                                        {{-- Permissions --}}
                                        <td data-label="{{ __('messages.PERMISSIONS header') }}" data-span="2" class="px-4 py-3">
                                            @php
                                                $enterprisePermissions = \App\Models\EnterpriseRolePermission::where('entreprise_id', Auth::user()->entreprise_id)
                                                    ->where('role_id', $role->id)
                                                    ->with('permission')
                                                    ->get()
                                                    ->pluck('permission');
                                            @endphp
                                            <div class="flex flex-wrap gap-1.5">
                                                @if ($enterprisePermissions->count() > 0)
                                                    @foreach ($enterprisePermissions->take(5) as $per)
                                                        @php
                                                            $permissionRawName = trim($per->name);
                                                            $permissionCandidates = array_values(array_unique([
                                                                $permissionRawName,
                                                                strtolower($permissionRawName),
                                                                strtoupper($permissionRawName),
                                                                str_replace(['_', '-'], ' ', $permissionRawName),
                                                                ucwords(strtolower(str_replace(['_', '-'], ' ', $permissionRawName))),
                                                                str_replace(' ', '_', $permissionRawName),
                                                                strtolower(str_replace(' ', '_', $permissionRawName)),
                                                                strtoupper(str_replace(' ', '_', $permissionRawName)),
                                                            ]));
                                                            $permissionLabel = $permissionRawName;
                                                            foreach ($permissionCandidates as $candidate) {
                                                                $candidateKey = 'messages.' . $candidate;
                                                                $translated = __($candidateKey);
                                                                if ($translated !== $candidateKey) {
                                                                    $permissionLabel = $translated;
                                                                    break;
                                                                }
                                                            }
                                                        @endphp
                                                        <span class="dt-badge dt-badge-navy">
                                                            <span class="dt-dot"></span>
                                                            {{ $permissionLabel }}
                                                        </span>
                                                    @endforeach
                                                    @if ($enterprisePermissions->count() > 5)
                                                        <span class="dt-badge dt-badge-neutral">
                                                            +{{ $enterprisePermissions->count() - 5 }} {{ __('messages.more') }}
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="dt-badge dt-badge-danger">
                                                        {{ __('messages.No permissions') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Actions --}}
                                        <td data-label="{{ __('messages.ACTIONS header') }}" data-span="2" class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                {{-- Edit --}}
                                                @if (Auth::user()->hasRole('Admin'))
                                                    <a href="{{ route('admin.roles.edit', $role->id) }}"
                                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white text-[#2D7080] hover:bg-[#2D7080]/10 hover:border-[#2D7080]/30 transition-colors"
                                                       title="{{ __('messages.Edit') }}">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                             class="h-4 w-4" fill="none"
                                                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M16.862 4.487a2.25 2.25 0 113.182 3.182L7.5 20.212 3 21l.788-4.5L16.862 4.487z"/>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-2.5 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl flex flex-col sm:flex-row items-center justify-between gap-2">
                      <span class="text-[12px] font-semibold text-slate-500">

                      </span>

                      <div class="flex items-center gap-1.5">
                        <button class="inline-flex h-8 px-3 items-center justify-center rounded-lg border border-slate-200 bg-white text-[12px] font-semibold text-slate-300 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 transition-colors" disabled>
                          {{ __('messages.Previous') }}
                        </button>
                        <button class="inline-flex h-8 min-w-8 px-2 items-center justify-center rounded-lg bg-[#0B2638] text-white text-[12px] font-bold">1</button>
                        <button class="inline-flex h-8 px-3 items-center justify-center rounded-lg border border-slate-200 bg-white text-[12px] font-semibold text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 transition-colors" disabled>
                          {{ __('messages.Next') }}
                        </button>
                      </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    @livewireScripts
</body>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
  const search = this.value.toLowerCase();
  document.querySelectorAll('#rolesTable tbody tr').forEach(row => {
    const roleName = row.querySelector('td:first-child').textContent.toLowerCase();
    row.style.display = roleName.includes(search) ? '' : 'none';
  });
});
</script>
</html>
