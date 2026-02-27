@extends('manager.layouts.app')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">{{ __('messages.Total users') }}</h2>
            </div>
            <a href="{{ route('manager.dashboard') }}"
               class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 hover:bg-gray-50">
                {{ __('messages.Back to list') }}
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Name') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Email') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Entreprise name') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Location') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @forelse($usersList as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-900">{{ $user->name }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $user->email }}</td>
                            <td class="px-4 py-2 text-gray-700">
                                @if ($user->entreprise)
                                    <a href="{{ route('manager.subscriptions.show', $user->entreprise) }}"
                                       class="text-blue-600 hover:text-blue-800 hover:underline">
                                        {{ $user->entreprise->name }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-2 text-gray-700">
                                @if ($user->last_login_country || $user->last_login_city)
                                    {{ $user->last_login_country }}
                                    @if ($user->last_login_city)
                                        — {{ $user->last_login_city }}
                                    @endif
                                @else
                                    {{ __('messages.Unknown location') }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-sm text-gray-500">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $usersList->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection


