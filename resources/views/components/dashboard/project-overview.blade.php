{{-- resources/views/components/team-members-table.blade.php --}}
@php
$members = [
    [
        'name' => 'Admin',
        'email' => 'Admin@gmail.com',
        'phone' => '0666666661',
        'salary' => '€200.00/month',
        'roles' => ['SuperAdmin', 'Admin'],
        'status' => 'Active',
        'type' => 'Full-time',
        'teams' => 'Not assigned',
        'currentTask' => 'No active task',
        'hours' => '0h 0m',
        'tasks' => '0/0',
        'days' => '0',
        'progress' => '0%',
        'limit' => '100h',
        'initials' => 'AD',
        'color' => 'bg-blue-500',
    ],
    [
        'name' => 'Alice Martin',
        'email' => 'alice@example.com',
        'phone' => '0666666661',
        'salary' => '€2,000.00/month',
        'roles' => ['Employee'],
        'status' => 'Active',
        'type' => 'Contractor',
        'teams' => 'Not assigned',
        'currentTask' => 'No active task',
        'hours' => '0h 0m',
        'tasks' => '0/0',
        'days' => '0',
        'progress' => '0%',
        'limit' => '160h',
        'initials' => 'AL',
        'color' => 'bg-blue-500',
    ],
];
@endphp

<div class="border-0 shadow-sm rounded-lg bg-white">
    <div class="border-b border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Team Members Overview</h3>
            <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg font-medium">+ Add employee</button>
        </div>
        <div class="mt-4">
            <input
                type="text"
                placeholder="Search employees..."
                class="w-full max-w-xs rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 placeholder-gray-500 focus:border-blue-500 focus:outline-none"
            />
        </div>
    </div>
    <div class="p-0">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            EMPLOYEE INFO
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            ROLE & STATUS
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            TEAMS & PROJECTS
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            CURRENT TASK
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            PROGRESS
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            MONTHLY STATS
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            UTILIZATION
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            ACTIONS
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($members as $index => $member)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold text-white {{ $member['color'] }}">
                                    {{ $member['initials'] }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $member['name'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $member['email'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $member['phone'] }}</p>
                                    <p class="text-xs font-medium text-emerald-600">{{ $member['salary'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-2">
                                <div class="flex gap-2">
                                    @foreach($member['roles'] as $role)
                                    <span class="inline-block px-2 py-1 text-xs font-medium rounded {{ $role === 'SuperAdmin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $role }}
                                    </span>
                                    @endforeach
                                </div>
                                <span class="inline-block px-2 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 rounded">
                                    {{ $member['status'] }}
                                </span>
                                <p class="text-xs text-gray-600">{{ $member['type'] }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-600">{{ $member['teams'] }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-600">{{ $member['currentTask'] }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-600">{{ $member['progress'] }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-1 text-xs text-gray-600">
                                <p>Hours: {{ $member['hours'] }}</p>
                                <p>Tasks: {{ $member['tasks'] }}</p>
                                <p>Days: {{ $member['days'] }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-1 text-xs text-gray-600">
                                <p>{{ $member['progress'] }}</p>
                                <p>Limit: {{ $member['limit'] }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <button class="text-gray-400 hover:text-gray-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                    </svg>
                                </button>
                                <button class="text-gray-400 hover:text-gray-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>