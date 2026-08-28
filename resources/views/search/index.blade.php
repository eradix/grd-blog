<x-layouts::public :title="'Search'">
    <h1 class="text-2xl font-bold">
        @if ($query !== '')
            Results for "{{ $query }}"
        @else
            Search
        @endif
    </h1>

    <div class="mt-6">
        @forelse ($posts as $post)
            <x-post-card :post="$post" />
        @empty
            <p class="text-zinc-500">
                @if ($query !== '')
                    No posts matched your search.
                @else
                    Enter a search term above to find posts.
                @endif
            </p>
        @endforelse
    </div>

    {{ $posts->links() }}
</x-layouts::public>
