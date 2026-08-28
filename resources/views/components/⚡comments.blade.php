<?php

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component
{
    public Post $post;

    #[Validate('required|string|max:2000')]
    public string $body = '';

    public function submit(): void
    {
        $user = auth()->user();

        abort_if($user === null, 403);

        $this->authorize('create', Comment::class);

        $key = "comment-submit:{$user->id}";

        if (RateLimiter::tooManyAttempts($key, maxAttempts: 5)) {
            $this->addError('body', 'Too many comments — please wait a moment before trying again.');

            return;
        }

        RateLimiter::hit($key, decaySeconds: 60);

        $this->validate();

        $this->post->comments()->create([
            'user_id' => $user->id,
            'body' => $this->body,
            'status' => CommentStatus::Pending,
        ]);

        $this->reset('body');

        session()->flash('comment-status', 'Thanks — your comment has been submitted and is awaiting review.');
    }

    public function with(): array
    {
        return [
            'comments' => $this->post->approvedComments()->with('author')->latest()->get(),
        ];
    }
};
?>

<div class="space-y-6">
    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
        {{ $comments->count() }} {{ Str::plural('Comment', $comments->count()) }}
    </h2>

    @if (session('comment-status'))
        <p class="rounded-md bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">
            {{ session('comment-status') }}
        </p>
    @endif

    @auth
        @can('create', Comment::class)
            <form wire:submit="submit" class="space-y-3">
                <textarea
                    wire:model="body"
                    rows="3"
                    placeholder="Add a comment..."
                    class="w-full rounded-md border-zinc-300 text-sm dark:border-zinc-700 dark:bg-zinc-900"
                ></textarea>
                @error('body')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <button
                    type="submit"
                    class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900"
                >
                    Post comment
                </button>
            </form>
        @else
            <p class="text-sm text-zinc-500">Verify your email address to leave a comment.</p>
        @endcan
    @else
        <p class="text-sm text-zinc-500">
            <a href="{{ route('login') }}" class="underline">Log in</a> to leave a comment.
        </p>
    @endauth

    <ul class="space-y-4">
        @forelse ($comments as $comment)
            <li class="border-t border-zinc-200 pt-4 dark:border-zinc-800">
                <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $comment->author->name }}</p>
                <p class="text-xs text-zinc-500">{{ $comment->created_at->diffForHumans() }}</p>
                <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300">{{ $comment->body }}</p>
            </li>
        @empty
            <li class="text-sm text-zinc-500">No comments yet.</li>
        @endforelse
    </ul>
</div>
