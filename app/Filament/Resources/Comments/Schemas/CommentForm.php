<?php

namespace App\Filament\Resources\Comments\Schemas;

use App\Enums\CommentStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CommentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('post.title')
                    ->label('Post')
                    ->disabled()
                    ->dehydrated(false),

                TextInput::make('author.name')
                    ->label('Author')
                    ->disabled()
                    ->dehydrated(false),

                Textarea::make('body')
                    ->disabled()
                    ->dehydrated(false)
                    ->rows(4)
                    ->columnSpanFull(),

                Select::make('status')
                    ->options(CommentStatus::class)
                    ->required(),
            ]);
    }
}
