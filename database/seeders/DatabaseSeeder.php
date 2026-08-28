<?php

namespace Database\Seeders;

use App\Enums\CommentStatus;
use App\Enums\Role;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Model events are intentionally left enabled (no WithoutModelEvents trait) — slug
     * generation, markdown rendering, and comment approval timestamps all run as saving
     * hooks, so seeded data needs to go through the same code path as the real app.
     */
    public function run(): void
    {
        $admin = User::factory()->role(Role::Admin)->create([
            'name' => 'Alex Admin',
            'email' => 'admin@example.com',
        ]);

        $editor = User::factory()->role(Role::Editor)->create([
            'name' => 'Erin Editor',
            'email' => 'editor@example.com',
        ]);

        $authors = User::factory()->role(Role::Author)->count(2)->create();

        $reader = User::factory()->role(Role::Reader)->create([
            'name' => 'Riley Reader',
            'email' => 'reader@example.com',
        ]);

        $categories = Category::factory()->count(5)->create();
        $tags = Tag::factory()->count(10)->create();
        $writers = $authors->push($admin, $editor);

        Post::factory()
            ->count(20)
            ->published()
            ->recycle($writers)
            ->recycle($categories)
            ->create()
            ->each(fn (Post $post) => $post->tags()->attach($tags->random(rand(1, 3))->pluck('id')));

        Post::factory()
            ->count(5)
            ->scheduled()
            ->recycle($writers)
            ->recycle($categories)
            ->create()
            ->each(fn (Post $post) => $post->tags()->attach($tags->random(rand(1, 3))->pluck('id')));

        Post::factory()
            ->count(5)
            ->recycle($writers)
            ->recycle($categories)
            ->create()
            ->each(fn (Post $post) => $post->tags()->attach($tags->random(rand(1, 3))->pluck('id')));

        $publishedPosts = Post::published()->get();
        $commenters = $writers->push($reader);

        Comment::factory()
            ->count(30)
            ->approved()
            ->recycle($publishedPosts)
            ->recycle($commenters)
            ->create();

        Comment::factory()
            ->count(10)
            ->recycle($publishedPosts)
            ->recycle($commenters)
            ->create(['status' => CommentStatus::Pending]);
    }
}
