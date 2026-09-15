<?php

namespace App\Http\Controllers;

use App\Models\Post;

class BlogController extends Controller
{
    /**
     * Show a list of published blog posts.
     */
    public function index()
    {
        $posts = Post::published()
            ->orderByDesc('published_at')
            ->paginate(10);

        return view('blog.index', compact('posts'));
    }

    /**
     * Show a single blog post by slug.
     */
    public function show(Post $post)
    {
        abort_unless($post->is_published, 404);

        return view('blog.show', compact('post'));
    }
}


