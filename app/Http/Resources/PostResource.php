<?php

namespace App\Http\Resources;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Detail shape for a single post — includes the rendered body. `body_html` is already
 * sanitized at write time (see App\Support\Markdown), so it's safe for a client to render
 * as-is.
 *
 * @mixin Post
 */
class PostResource extends JsonResource
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
            'body_html' => $this->body_html,
            'featured_image_url' => $this->featured_image_path
                ? asset('storage/'.$this->featured_image_path)
                : null,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
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
