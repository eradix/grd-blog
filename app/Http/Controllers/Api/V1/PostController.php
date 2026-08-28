<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Resources\PostSummaryResource;
use App\Models\Post;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $posts = Post::published()
            ->withPublicRelations()
            ->latest('published_at')
            ->simplePaginate(12);

        return PostSummaryResource::collection($posts);
    }

    /**
     * A draft or a future-dated post is a genuine 404 to API clients — never merely hidden.
     */
    public function show(string $slug): PostResource
    {
        $post = Post::published()
            ->withPublicRelations()
            ->where('slug', $slug)
            ->firstOrFail();

        return new PostResource($post);
    }
}
