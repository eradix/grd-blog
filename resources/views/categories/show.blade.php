<x-layouts::public :title="$category->name">
    <h1 class="text-2xl font-bold">{{ $category->name }}</h1>

    @if ($category->description)
        <p class="mt-2 text-zinc-600 dark:text-zinc-400">{{ $category->description }}</p>
    @endif

    <div class="mt-6">
        @forelse ($posts as $post)
            <x-post-card :post="$post" />
        @empty
            <p class="text-zinc-500">No posts in this category yet.</p>
        @endforelse
    </div>

    {{ $posts->links() }}
</x-layouts::public>
