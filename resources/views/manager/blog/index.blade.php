@extends('manager.layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto">
        {{-- Page heading --}}
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
            <div class="min-w-0">
                <p class="dt-overline mb-1">{{ __('messages.Workspace') }}</p>
                <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">{{ __('messages.Manager Blog posts title') }}</h1>
                <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.Manager Blog posts subtitle') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('manager.blog.create') }}" class="dt-btn dt-btn-teal">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('messages.Manager Blog new post') }}
                </a>
            </div>
        </div>

        @if(session('status'))
            <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if($posts->isEmpty())
            <div class="dt-card p-8 text-center">
                <div class="dt-empty-icon mx-auto mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9M16.5 3.5a2.121 2.121 0 013 3L9 17l-4 1 1-4 10.5-10.5z" />
                    </svg>
                </div>
                <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Manager Blog empty') }}</p>
            </div>
        @else
            <div class="dt-card overflow-hidden">
                <div class="manager-table-wrapper overflow-x-auto">
                    <table class="manager-table dt-table min-w-full">
                        <thead>
                        <tr>
                            <th>{{ __('messages.Manager Blog table title') }}</th>
                            <th>{{ __('messages.Manager Blog table slug') }}</th>
                            <th>{{ __('messages.Manager Blog table status') }}</th>
                            <th>{{ __('messages.Manager Blog table published_at') }}</th>
                            <th class="text-right">{{ __('messages.Manager Blog table actions') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($posts as $post)
                            <tr class="manager-card-row" aria-expanded="true">
                                <td data-label="{{ __('messages.Manager Blog table title') }}" data-span="2" data-manager-card-toggle class="manager-toggle-cell">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <a href="{{ route('manager.blog.edit', $post) }}" class="font-semibold text-[13px] text-[#0B2638] hover:text-brand-secondary hover:underline">
                                                {{ $post->title }}
                                            </a>
                                        </div>
                                        <span class="shrink-0 mt-0.5 text-slate-400 min-[1366px]:hidden" aria-hidden="true">
                                            <svg class="manager-chevron w-5 h-5" viewBox="0 0 24 24" fill="none">
                                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="{{ __('messages.Manager Blog table slug') }}" data-span="2">
                                    <code class="text-xs bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded">{{ $post->slug }}</code>
                                </td>
                                <td data-label="{{ __('messages.Manager Blog table status') }}">
                                    @if($post->is_published)
                                        <span class="dt-badge dt-badge-success"><span class="dt-dot"></span>{{ __('messages.Manager Blog status published') }}</span>
                                    @else
                                        <span class="dt-badge dt-badge-neutral"><span class="dt-dot"></span>{{ __('messages.Manager Blog status draft') }}</span>
                                    @endif
                                </td>
                                <td data-label="{{ __('messages.Manager Blog table published_at') }}" class="text-slate-500 text-[13px]">
                                    {{ optional($post->published_at)->format('Y-m-d H:i') ?? '—' }}
                                </td>
                                <td data-label="{{ __('messages.Manager Blog table actions') }}" data-span="2">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
                                           title="{{ __('messages.Manager Blog action view') }}"
                                           class="inline-flex items-center justify-center w-[30px] h-[30px] rounded-lg border border-slate-200 bg-white text-slate-500 hover:border-[#2D7080] hover:text-[#2D7080] hover:bg-[#f0f7f8] transition-colors flex-shrink-0">
                                            <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('manager.blog.edit', $post) }}"
                                           title="{{ __('messages.Manager Blog action edit') }}"
                                           class="inline-flex items-center justify-center w-[30px] h-[30px] rounded-lg border border-slate-200 bg-white text-slate-500 hover:border-[#2D7080] hover:text-[#2D7080] hover:bg-[#f0f7f8] transition-colors flex-shrink-0">
                                            <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                        <form action="{{ route('manager.blog.destroy', $post) }}" method="POST"
                                              class="inline-flex flex-shrink-0"
                                              onsubmit="return confirm('{{ __('messages.Manager Blog delete confirm') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    title="{{ __('messages.Manager Blog action delete') }}"
                                                    class="inline-flex items-center justify-center w-[30px] h-[30px] rounded-lg border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 hover:border-red-300 transition-colors">
                                                <svg class="w-[15px] h-[15px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-slate-100">
                    {{ $posts->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection
