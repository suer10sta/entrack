@extends('manager.layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.Manager Blog new title') }}</h1>
            <a href="{{ route('manager.blog.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                ← {{ __('messages.Manager Blog back to posts') }}
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('manager.blog.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Manager Blog field title') }}</label>
                <input type="text" name="title" value="{{ old('title') }}"
                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                       required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Manager Blog field slug optional') }}</label>
                    <input type="text" name="slug" value="{{ old('slug') }}"
                           placeholder="{{ __('messages.Manager Blog slug placeholder') }}"
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Manager Blog publish datetime') }}</label>
                    <input type="datetime-local" name="published_at" value="{{ old('published_at') }}"
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Manager Blog excerpt label') }}</label>
                <textarea name="excerpt" rows="2"
                          class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">{{ old('excerpt') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Manager Blog content label') }}</label>
                <textarea id="blog-body" name="body" rows="12"
                          class="block w-full rounded-lg border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">{{ old('body') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">{{ __('messages.Manager Blog content helper') }}</p>
            </div>

            <div class="flex items-center justify-between">
                <label class="inline-flex items-center space-x-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_published" value="1" class="rounded border-gray-300 text-blue-600"
                        {{ old('is_published', true) ? 'checked' : '' }}>
                    <span>{{ __('messages.Manager Blog publish immediately') }}</span>
                </label>

                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    {{ __('messages.Manager Blog save post') }}
                </button>
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
