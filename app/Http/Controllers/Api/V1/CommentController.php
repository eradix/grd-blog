<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\CommentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CommentController extends Controller
{
    public function index(string $slug): AnonymousResourceCollection
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        $comments = $post->approvedComments()
            ->with('author')
            ->latest()
            ->simplePaginate(20);

        return CommentResource::collection($comments);
    }

    /**
     * Authorization (verified email required) is enforced in StoreCommentRequest via
     * CommentPolicy::create, and the route applies the shared "comments" rate limiter.
     */
    public function store(StoreCommentRequest $request, string $slug): JsonResponse
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
            'status' => CommentStatus::Pending,
        ]);

        return (new CommentResource($comment))
            ->additional(['message' => 'Comment submitted and awaiting review.'])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
