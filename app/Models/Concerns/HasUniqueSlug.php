<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Generates a slug from a source attribute (default: "name") on create, and keeps it
 * unique against this model's own table. Slugs are never accepted from user input.
 *
 * @mixin Model
 */
trait HasUniqueSlug
{
    public static function bootHasUniqueSlug(): void
    {
        static::creating(function ($model) {
            if (filled($model->slug)) {
                return;
            }

            $base = Str::slug($model->{$model->slugSourceColumn()});
            $slug = $base;
            $suffix = 2;

            while (static::query()->where('slug', $slug)->exists()) {
                $slug = "{$base}-{$suffix}";
                $suffix++;
            }

            $model->slug = $slug;
        });
    }

    protected function slugSourceColumn(): string
    {
        return 'name';
    }
}
