<?php

namespace App\Filament\Resources\Comments\Tables;

use App\Enums\CommentStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post.title')
                    ->label('Post')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable(),

                TextColumn::make('body')
                    ->limit(80)
                    ->wrap(),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(CommentStatus::class)
                    ->default(CommentStatus::Pending->value),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('approve')
                    ->icon(Heroicon::Check)
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== CommentStatus::Approved)
                    ->action(fn ($record) => $record->update(['status' => CommentStatus::Approved])),

                Action::make('markSpam')
                    ->label('Mark spam')
                    ->icon(Heroicon::NoSymbol)
                    ->color('danger')
                    ->visible(fn ($record) => $record->status !== CommentStatus::Spam)
                    ->action(fn ($record) => $record->update(['status' => CommentStatus::Spam])),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approve')
                        ->icon(Heroicon::Check)
                        ->color('success')
                        ->action(fn (Collection $records) => $records->each->update(['status' => CommentStatus::Approved]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
