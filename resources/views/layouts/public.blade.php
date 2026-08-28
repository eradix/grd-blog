@php
    $navCategories = \App\Support\SiteNav::categories();
    $navTags = \App\Support\SiteNav::tags();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        @include('partials.head')
    </head>
    <body class="flex h-full min-h-screen flex-col bg-white text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <header class="border-b border-zinc-200 dark:border-zinc-800">
            <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-4 px-4 py-4">
                <a href="{{ route('home') }}" class="text-lg font-semibold">
                    {{ config('app.name') }}
                </a>

                <nav class="flex flex-wrap items-center gap-4 text-sm">
                    @foreach ($navCategories as $category)
                        <a href="{{ route('categories.show', $category['slug']) }}" class="text-zinc-600 hover:underline dark:text-zinc-400">
                            {{ $category['name'] }}
                        </a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-4">
                    <form action="{{ route('search') }}" method="GET" class="flex items-center">
                        <input
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Search posts..."
                            class="w-40 rounded-md border-zinc-300 text-sm dark:border-zinc-700 dark:bg-zinc-900 sm:w-56"
                        />
                    </form>

                    @auth
                        <div class="flex items-center gap-3 text-sm">
                            @if (auth()->user()->role->canAccessAdmin())
                                <a href="{{ url('/admin') }}" class="text-zinc-600 hover:underline dark:text-zinc-400">Admin</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-zinc-600 hover:underline dark:text-zinc-400">Log out</button>
                            </form>
                        </div>
                    @else
                        <div class="flex items-center gap-3 text-sm">
                            <a href="{{ route('login') }}" class="text-zinc-600 hover:underline dark:text-zinc-400">Log in</a>
                            <a href="{{ route('register') }}" class="text-zinc-600 hover:underline dark:text-zinc-400">Register</a>
                        </div>
                    @endauth
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-5xl flex-1 px-4 py-10">
            {{ $slot }}
        </main>

        <footer class="border-t border-zinc-200 py-8 dark:border-zinc-800">
            <div class="mx-auto max-w-5xl px-4">
                @if (! empty($navTags))
                    <div class="flex flex-wrap gap-2">
                        @foreach ($navTags as $tag)
                            <a
                                href="{{ route('tags.show', $tag['slug']) }}"
                                class="rounded-full bg-zinc-100 px-3 py-1 text-xs text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800"
                            >
                                {{ $tag['name'] }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </footer>
    </body>
</html>
