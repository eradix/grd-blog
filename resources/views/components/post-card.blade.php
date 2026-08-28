@props(['post'])

<article class="border-b border-zinc-200 py-6 dark:border-zinc-800">
    <a href="{{ route('posts.show', $post->slug) }}" class="text-xl font-semibold hover:underline">
        {{ $post->title }}
    </a>

    <p class="mt-1 text-sm text-zinc-500">
        {{ $post->author->name }}
        @if ($post->category)
            in <a href="{{ route('categories.show', $post->category->slug) }}" class="hover:underline">{{ $post->category->name }}</a>
        @endif
        &middot; {{ $post->published_at->format('M j, Y') }}
    </p>

    @if ($post->excerpt)
        <p class="mt-3 text-zinc-700 dark:text-zinc-300">{{ $post->excerpt }}</p>
    @endif

    @if ($post->tags->isNotEmpty())
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach ($post->tags as $tag)
                <a
                    href="{{ route('tags.show', $tag->slug) }}"
                    class="rounded-full bg-zinc-100 px-3 py-1 text-xs text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800"
                >
                    {{ $tag->name }}
                </a>
            @endforeach
        </div>
    @endif
</article>
