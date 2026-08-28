<?php

namespace App\Observers;

use App\Models\Tag;
use Illuminate\Support\Facades\Cache;

class TagObserver
{
    public function saved(Tag $tag): void
    {
        Cache::forget('nav.tags');
    }

    public function deleted(Tag $tag): void
    {
        Cache::forget('nav.tags');
    }
}
