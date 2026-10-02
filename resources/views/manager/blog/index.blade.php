@extends('manager.layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-[22px] font-bold tracking-tight text-[#0B2638]">{{ __('messages.Manager Blog posts title') }}</h1>
                <p class="text-sm text-gray-500">{{ __('messages.Manager Blog posts subtitle') }}</p>
            </div>
            <a href="{{ route('manager.blog.create') }}"
               class="inline-flex items-center px-4 py-2 bg-[#2D7080] text-white text-sm font-medium rounded-lg hover:bg-[#245f6d] transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('messages.Manager Blog new post') }}
            </a>
        </div>

        @if(session('status'))
            <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if($posts->isEmpty())
            <div class="bg-white border border-gray-200 rounded-xl p-8 text-center text-gray-500">
                {{ __('messages.Manager Blog empty') }}
            </div>
        @else
            <div class="manager-table-wrapper bg-white border border-gray-200 rounded-xl overflow-hidden">
                <table class="manager-table min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">{{ __('messages.Manager Blog table title') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">{{ __('messages.Manager Blog table slug') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">{{ __('messages.Manager Blog table status') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">{{ __('messages.Manager Blog table published_at') }}</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">{{ __('messages.Manager Blog table actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @foreach($posts as $post)
                        <tr class="manager-card-row" aria-expanded="true">
                            <td data-label="{{ __('messages.Manager Blog table title') }}" data-span="2" data-manager-card-toggle class="manager-toggle-cell px-4 py-3 text-gray-900">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <a href="{{ route('manager.blog.edit', $post) }}" class="font-semibold hover:text-[#0B2638]">
                                            {{ $post->title }}
                                        </a>
                                    </div>
                                    <span class="shrink-0 mt-0.5 text-slate-400 lg:hidden" aria-hidden="true">
                                        <svg class="manager-chevron w-5 h-5" viewBox="0 0 24 24" fill="none">
                                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                            </td>
                            <td data-label="{{ __('messages.Manager Blog table slug') }}" data-span="2" class="px-4 py-3 text-gray-500">
                                <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">{{ $post->slug }}</code>
                            </td>
                            <td data-label="{{ __('messages.Manager Blog table status') }}" class="px-4 py-3">
                                @if($post->is_published)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">
                                        {{ __('messages.Manager Blog status published') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                        {{ __('messages.Manager Blog status draft') }}
                                    </span>
                                @endif
                            </td>
                            <td data-label="{{ __('messages.Manager Blog table published_at') }}" class="px-4 py-3 text-gray-500">
                                {{ optional($post->published_at)->format('Y-m-d H:i') ?? '—' }}
                            </td>
                            <td data-label="{{ __('messages.Manager Blog table actions') }}" data-span="2" class="px-4 py-3 text-right space-x-2">
                                <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
                                   class="inline-flex items-center px-2 py-1 text-xs text-gray-600 hover:text-[#0B2638]">
                                    {{ __('messages.Manager Blog action view') }}
                                </a>
                                <a href="{{ route('manager.blog.edit', $post) }}"
                                   class="inline-flex items-center px-2 py-1 text-xs text-gray-600 hover:text-[#0B2638]">
                                    {{ __('messages.Manager Blog action edit') }}
                                </a>
                                <form action="{{ route('manager.blog.destroy', $post) }}" method="POST"
                                      class="inline-block"
                                      onsubmit="return confirm('{{ __('messages.Manager Blog delete confirm') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center px-2 py-1 text-xs text-red-600 hover:text-red-700">
                                        {{ __('messages.Manager Blog action delete') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                    {{ $posts->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection



