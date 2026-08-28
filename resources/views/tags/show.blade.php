<x-layouts::public :title="'#'.$tag->name">
    <h1 class="text-2xl font-bold">#{{ $tag->name }}</h1>

    <div class="mt-6">
        @forelse ($posts as $post)
            <x-post-card :post="$post" />
        @empty
            <p class="text-zinc-500">No posts tagged with this yet.</p>
        @endforelse
    </div>

    {{ $posts->links() }}
</x-layouts::public>
