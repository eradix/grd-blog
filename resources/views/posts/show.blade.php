<x-layouts::public :title="$post->title">
    <article>
        <h1 class="text-3xl font-bold">{{ $post->title }}</h1>

        <p class="mt-2 text-sm text-zinc-500">
            {{ $post->author->name }}
            @if ($post->category)
                in <a href="{{ route('categories.show', $post->category->slug) }}" class="hover:underline">{{ $post->category->name }}</a>
            @endif
            &middot; {{ $post->published_at->format('M j, Y') }}
        </p>

        @if ($post->featured_image_path)
            <img
                src="{{ asset('storage/'.$post->featured_image_path) }}"
                alt="{{ $post->title }}"
                class="mt-6 w-full rounded-lg"
            />
        @endif

        <div class="prose prose-zinc mt-8 max-w-none dark:prose-invert">
            {!! $post->body_html !!}
        </div>

        @if ($post->tags->isNotEmpty())
            <div class="mt-6 flex flex-wrap gap-2">
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

    <div class="mt-12 border-t border-zinc-200 pt-8 dark:border-zinc-800">
        <livewire:comments :post="$post" />
    </div>
</x-layouts::public>
