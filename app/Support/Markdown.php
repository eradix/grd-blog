<?php

namespace App\Support;

use Illuminate\Support\Str;

class Markdown
{
    /**
     * Render author-supplied markdown to safe HTML. Raw HTML in the source is stripped and
     * unsafe link schemes (javascript:, data:, ...) are rejected, so the result is the only
     * value in the app that is ever echoed unescaped.
     */
    public static function toSafeHtml(string $source): string
    {
        return Str::markdown($source, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
