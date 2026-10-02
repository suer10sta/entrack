@extends('manager.layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-[22px] font-bold tracking-tight text-[#0B2638]">{{ __('messages.Manager Blog edit title') }}</h1>
                <p class="text-sm text-gray-500">
                    {{ __('messages.Manager Blog editing label') }}
                    <span class="font-semibold">{{ $post->title }}</span>
                </p>
            </div>
            <a href="{{ route('manager.blog.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                ← {{ __('messages.Manager Blog back to posts') }}
            </a>
        </div>

        @if(session('status'))
            <div class="mb-4 rounded-md bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('manager.blog.update', $post) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Manager Blog field title') }}</label>
                <input type="text" name="title" value="{{ old('title', $post->title) }}"
                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#2D7080] focus:border-[#2D7080] text-sm"
                       required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Manager Blog field slug') }}</label>
                    <input type="text" name="slug" value="{{ old('slug', $post->slug) }}"
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#2D7080] focus:border-[#2D7080] text-sm">
                    <p class="mt-1 text-xs text-gray-500">{{ __('messages.Manager Blog slug helper') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Manager Blog publish datetime') }}</label>
                    <input type="datetime-local" name="published_at"
                           value="{{ old('published_at', optional($post->published_at)->format('Y-m-d\TH:i')) }}"
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#2D7080] focus:border-[#2D7080] text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Manager Blog excerpt label') }}</label>
                <textarea name="excerpt" rows="2"
                          class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#2D7080] focus:border-[#2D7080] text-sm">{{ old('excerpt', $post->excerpt) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Manager Blog content label') }}</label>
                <textarea id="blog-body" name="body" rows="12"
                          class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-[#2D7080] focus:border-[#2D7080] text-sm">{{ old('body', $post->body) }}</textarea>
                <p class="mt-1 text-xs text-gray-500">{{ __('messages.Manager Blog content helper') }}</p>
            </div>

            <div class="flex items-center justify-between">
                <label class="inline-flex items-center space-x-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_published" value="1" class="rounded border-gray-300 text-[#2D7080]"
                        {{ old('is_published', $post->is_published) ? 'checked' : '' }}>
                    <span>{{ __('messages.Manager Blog status published') }}</span>
                </label>

                <div class="space-x-2">
                    <a href="{{ route('blog.show', $post->slug) }}" target="_blank"
                       class="inline-flex items-center px-3 py-2 text-sm text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        {{ __('messages.Manager Blog view public page') }}
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-[#2D7080] text-white text-sm font-medium rounded-lg hover:bg-[#245f6d] transition-colors">
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

