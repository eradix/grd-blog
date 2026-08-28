<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostSummaryResource;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TagController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TagResource::collection(Tag::orderBy('name')->get());
    }

    public function show(string $slug): AnonymousResourceCollection
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $posts = $tag->posts()
            ->published()
            ->withPublicRelations()
            ->latest('published_at')
            ->simplePaginate(12);

        return PostSummaryResource::collection($posts)
            ->additional(['tag' => new TagResource($tag)]);
    }
}
