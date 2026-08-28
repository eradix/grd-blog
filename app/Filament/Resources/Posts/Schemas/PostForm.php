<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Tag;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $state, callable $set, ?string $old, string $operation) {
                                if ($operation === 'create') {
                                    $set('slug', Str::slug($state));
                                }
                            })
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('Auto-filled from the title on create; edit only if you know what you are doing.'),

                        Select::make('user_id')
                            ->label('Author')
                            ->relationship('author', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->default(fn () => Filament::auth()->id()),

                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required()->maxLength(255),
                            ])
                            ->createOptionUsing(fn (array $data) => Category::create($data)->id),

                        Select::make('tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')->required()->maxLength(255),
                            ])
                            ->createOptionUsing(fn (array $data) => Tag::create($data)->id),

                        Textarea::make('excerpt')
                            ->maxLength(500)
                            ->rows(2)
                            ->columnSpanFull(),

                        MarkdownEditor::make('body')
                            ->required()
                            ->columnSpanFull(),

                        FileUpload::make('featured_image_path')
                            ->label('Featured image')
                            ->image()
                            ->disk('public')
                            ->directory('posts')
                            ->maxSize(2048)
                            ->columnSpanFull(),
                    ]),

                Section::make('Publishing')
                    ->columns(2)
                    ->components([
                        Select::make('status')
                            ->options(PostStatus::class)
                            ->required()
                            ->default(PostStatus::Draft),

                        DateTimePicker::make('published_at')
                            ->helperText('A future date schedules the post; it stays hidden until then.')
                            ->required(fn (callable $get) => $get('status') === PostStatus::Published->value),
                    ]),

                Section::make('SEO')
                    ->columns(2)
                    ->collapsed()
                    ->components([
                        TextInput::make('meta_title')->maxLength(255),
                        TextInput::make('meta_description')->maxLength(255),
                    ]),
            ]);
    }
}
