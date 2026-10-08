@extends('manager.layouts.app')

@section('content')
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
    <div class="min-w-0">
        <p class="dt-overline mb-1">{{ __('messages.Manager area') }}</p>
        <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">
            {{ __('messages.Total users') }}
        </h1>
        <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.All registered users on the platform.') }}</p>
    </div>
    <a href="{{ route('manager.dashboard') }}" class="dt-btn dt-btn-ghost flex-shrink-0">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ __('messages.Back') }}
    </a>
</div>

<div class="dt-card overflow-hidden">
    <div class="px-5 sm:px-6 py-4 border-b border-slate-100 bg-white">
        <h2 class="dt-section-title">{{ __('messages.Users') }}</h2>
        <p class="dt-section-subtitle mt-0.5">{{ $usersList->total() }} {{ __('messages.total') }}</p>
    </div>

    <div class="manager-table-wrapper overflow-x-auto">
        <table class="dt-table manager-table min-w-full">
            <thead>
                <tr class="dt-table-head">
                    <th class="px-4 py-2.5">{{ __('messages.Name') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Email') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Entreprise name') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Location') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($usersList as $user)
                    <tr class="manager-card-row" aria-expanded="true">
                        <td data-label="{{ __('messages.Name') }}" data-span="2" data-manager-card-toggle class="manager-toggle-cell px-4 py-2.5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 flex-shrink-0 rounded-full bg-[#0B2638]/[0.06] text-[#0B2638] flex items-center justify-center text-[12px] font-bold">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="text-[13px] font-semibold text-slate-800 truncate">{{ $user->name }}</span>
                                </div>
                                <span class="shrink-0 mt-0.5 text-slate-400 min-[1366px]:hidden">
                                    <svg class="manager-chevron w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </div>
                        </td>
                        <td data-label="{{ __('messages.Email') }}" class="px-4 py-2.5 text-[13px] text-slate-500">{{ $user->email }}</td>
                        <td data-label="{{ __('messages.Entreprise name') }}" class="px-4 py-2.5 text-[13px]">
                            @if($user->entreprise)
                                <a href="{{ route('manager.subscriptions.show', $user->entreprise) }}"
                                   class="text-[#2D7080] hover:text-[#0B2638] hover:underline font-medium">
                                    {{ $user->entreprise->name }}
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td data-label="{{ __('messages.Location') }}" class="px-4 py-2.5 text-[13px] text-slate-600">
                            @if($user->last_login_country || $user->last_login_city)
                                {{ $user->last_login_country }}{{ $user->last_login_city ? ' — ' . $user->last_login_city : '' }}
                            @else
                                <span class="text-slate-400">{{ __('messages.Unknown location') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <div class="dt-empty-icon mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                                </svg>
                            </div>
                            <p class="text-[13.5px] font-semibold text-[#0B2638]">{{ __('messages.No users found.') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($usersList->hasPages())
        <div class="px-5 sm:px-6 py-3 border-t border-slate-100 bg-[#f8fafc]">
            {{ $usersList->links() }}
        </div>
    @endif
</div>
@endsection
