<?php

namespace App\Filament\Widgets;

use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Models\Comment;
use App\Models\Post;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BlogStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Published posts', Post::query()->where('status', PostStatus::Published)->count()),
            Stat::make('Draft posts', Post::query()->where('status', PostStatus::Draft)->count()),
            Stat::make('Comments awaiting review', Comment::query()->where('status', CommentStatus::Pending)->count())
                ->color('warning'),
        ];
    }
}
