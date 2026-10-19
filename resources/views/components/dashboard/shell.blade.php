{{-- Shared dashboard frame; role-specific menus and page content stay separate. --}}
@if(Auth::user()->hasRole('Admin'))
    <x-dashboard.sidebar />
@else
    <x-employees.sidebar />
@endif
<div class="flex flex-col flex-1 min-w-0 overflow-y-auto">
    <x-dashboard.navbar />
    {{ $slot }}
</div>
