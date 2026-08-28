<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Contracts\View\View;

class PostController extends Controller
{
    public function index(): View
    {
        $posts = Post::published()
            ->withPublicRelations()
            ->latest('published_at')
            ->simplePaginate(12);

        return view('posts.index', compact('posts'));
    }

    /**
     * A draft or a future-dated post is a genuine 404 to the public — never merely hidden.
     */
    public function show(string $slug): View
    {
        $post = Post::published()
            ->withPublicRelations()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('posts.show', compact('post'));
    }
}
