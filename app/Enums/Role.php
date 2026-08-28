<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Role: string implements HasLabel
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Author = 'author';
    case Reader = 'reader';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Editor => 'Editor',
            self::Author => 'Author',
            self::Reader => 'Reader',
        };
    }

    /**
     * Admins and editors manage every post and moderate comments.
     */
    public function isStaff(): bool
    {
        return match ($this) {
            self::Admin, self::Editor => true,
            self::Author, self::Reader => false,
        };
    }

    /**
     * Roles allowed into the Filament panel; readers are excluded even with a valid session.
     */
    public function canAccessAdmin(): bool
    {
        return $this !== self::Reader;
    }
}
