<x-layouts::public :title="null">
    <h1 class="text-2xl font-bold">Latest posts</h1>

    <div class="mt-6">
        @forelse ($posts as $post)
            <x-post-card :post="$post" />
        @empty
            <p class="text-zinc-500">No posts published yet.</p>
        @endforelse
    </div>

    {{ $posts->links() }}
</x-layouts::public>
