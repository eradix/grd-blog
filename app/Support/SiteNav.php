<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;

/**
 * Cached category/tag lists for the public site's nav and footer. Small, rarely-changing
 * tables, so a plain time-based cache (invalidated by CategoryObserver/TagObserver on write)
 * is enough to keep them off the hot path of every page load.
 *
 * Cached as plain arrays, not Eloquent collections — the file cache driver serializes
 * with PHP's serialize(), and only scalars/arrays are guaranteed to round-trip cleanly.
 */
class SiteNav
{
    /**
     * @return array<int, array{id: int, name: string, slug: string}>
     */
    public static function categories(): array
    {
        return Cache::remember(
            'nav.categories',
            now()->addHour(),
            fn () => Category::orderBy('name')->get(['id', 'name', 'slug'])->toArray(),
        );
    }

    /**
     * @return array<int, array{id: int, name: string, slug: string}>
     */
    public static function tags(): array
    {
        return Cache::remember(
            'nav.tags',
            now()->addHour(),
            fn () => Tag::orderBy('name')->get(['id', 'name', 'slug'])->toArray(),
        );
    }
}
