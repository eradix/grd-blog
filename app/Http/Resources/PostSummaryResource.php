<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * List-view shape for a post — no body. Used on index/archive/search endpoints, where
 * clients render a title, excerpt, and metadata, never the full content.
 *
 * @mixin Post
 */
class PostSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'published_at' => $this->published_at?->toIso8601String(),
            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ],
            'category' => $this->category ? new CategoryResource($this->category) : null,
            'tags' => TagResource::collection($this->tags),
            'comments_count' => $this->approved_comments_count,
        ];
    }
}
