<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Contracts\View\View;

class TagController extends Controller
{
    public function show(string $slug): View
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $posts = $tag->posts()
            ->published()
            ->withPublicRelations()
            ->latest('published_at')
            ->simplePaginate(12);

        return view('tags.show', compact('tag', 'posts'));
    }
}
