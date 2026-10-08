@extends('manager.layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Page heading --}}
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
            <div class="min-w-0">
                <p class="dt-overline mb-1">{{ __('messages.Workspace') }}</p>
                <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">{{ __('messages.Manager Blog edit title') }}</h1>
                <p class="text-[13.5px] text-slate-500 mt-1">
                    {{ __('messages.Manager Blog editing label') }}
                    <span class="font-semibold text-slate-700">{{ $post->title }}</span>
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('manager.blog.index') }}" class="dt-btn dt-btn-ghost">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                    {{ __('messages.Manager Blog back to posts') }}
                </a>
            </div>
        </div>

        @if(session('status'))
            <div class="mb-4 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('manager.blog.update', $post) }}" method="POST" class="dt-card p-5 sm:p-6 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Manager Blog field title') }}</label>
                <input type="text" name="title" value="{{ old('title', $post->title) }}"
                       class="block w-full rounded-xl border-slate-200 bg-white text-slate-800 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                       required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Manager Blog field slug') }}</label>
                    <input type="text" name="slug" value="{{ old('slug', $post->slug) }}"
                           class="block w-full rounded-xl border-slate-200 bg-white text-slate-800 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                    <p class="mt-1 text-xs text-slate-500">{{ __('messages.Manager Blog slug helper') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Manager Blog publish datetime') }}</label>
                    <input type="datetime-local" name="published_at"
                           value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\TH:i')) }}"
                           class="block w-full rounded-xl border-slate-200 bg-white text-slate-800 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Manager Blog excerpt label') }}</label>
                <textarea name="excerpt" rows="2"
                          class="block w-full rounded-xl border-slate-200 bg-white text-slate-800 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3 resize-y">{{ old('excerpt', $post->excerpt) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Manager Blog content label') }}</label>
                <textarea id="blog-body" name="body" rows="12"
                          class="block w-full rounded-xl border-slate-200 bg-white text-slate-800 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">{{ old('body', $post->body) }}</textarea>
                <p class="mt-1 text-xs text-slate-500">{{ __('messages.Manager Blog content helper') }}</p>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-1">
                <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_published" value="1" class="rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/25"
                        {{ old('is_published', $post->is_published) ? 'checked' : '' }}>
                    <span>{{ __('messages.Manager Blog status published') }}</span>
                </label>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank" class="dt-btn dt-btn-ghost">
                        {{ __('messages.Manager Blog view public page') }}
                    </a>
                    <button type="submit" class="dt-btn dt-btn-teal">
                        {{ __('messages.Manager Blog save changes') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.ClassicEditor) {
                ClassicEditor
                    .create(document.querySelector('#blog-body'), {
                        toolbar: ['undo', 'redo', '|', 'bold', 'italic', '|', 'bulletedList', 'numberedList', '|', 'link', 'removeFormat']
                    })
                    .catch(error => {
                        console.error(error);
                    });
            }
        });
    </script>
@endpush
