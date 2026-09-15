<?php

namespace App\Http\Controllers\manager;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ManagerBlogController extends Controller
{
    public function index()
    {
        $posts = Post::orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('manager.blog.index', compact('posts'));
    }

    public function create()
    {
        return view('manager.blog.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $slug = $data['slug'] ?: Str::slug($data['title']);
        if (Post::where('slug', $slug)->exists()) {
            $slug .= '-' . uniqid();
        }

        $post = Post::create([
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'] ?? null,
            'body' => $data['body'],
            'is_published' => (bool) ($data['is_published'] ?? false),
            'published_at' => $data['published_at'] ?? (now()),
        ]);

        return redirect()
            ->route('manager.blog.edit', $post)
            ->with('status', 'Blog post created successfully.');
    }

    public function edit(Post $post)
    {
        return view('manager.blog.edit', compact('post'));
    }

    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $slug = $data['slug'] ?: $post->slug ?: Str::slug($data['title']);
        if ($slug !== $post->slug && Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
            $slug .= '-' . uniqid();
        }

        $post->update([
            'title' => $data['title'],
            'slug' => $slug,
            'excerpt' => $data['excerpt'] ?? null,
            'body' => $data['body'],
            'is_published' => (bool) ($data['is_published'] ?? false),
            'published_at' => $data['published_at'] ?? $post->published_at ?? now(),
        ]);

        return redirect()
            ->route('manager.blog.edit', $post)
            ->with('status', 'Blog post updated successfully.');
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()
            ->route('manager.blog.index')
            ->with('status', 'Blog post deleted.');
    }
}


