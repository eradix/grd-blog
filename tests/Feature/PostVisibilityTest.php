<?php

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

test('a draft post 404s on the public show route', function () {
    $post = Post::factory()->for(User::factory(), 'author')->create([
        'status' => PostStatus::Draft,
        'published_at' => null,
    ]);

    $this->get("/posts/{$post->slug}")->assertNotFound();
});

test('a future-dated published post 404s on the public show route', function () {
    $post = Post::factory()->for(User::factory(), 'author')->create([
        'status' => PostStatus::Published,
        'published_at' => now()->addDay(),
    ]);

    $this->get("/posts/{$post->slug}")->assertNotFound();
});

test('a published, due post is visible on the public show route', function () {
    $post = Post::factory()->for(User::factory(), 'author')->published()->create();

    $this->get("/posts/{$post->slug}")->assertOk()->assertSee($post->title);
});

test('the feed only lists published, due posts', function () {
    $visible = Post::factory()->for(User::factory(), 'author')->published()->create();
    Post::factory()->for(User::factory(), 'author')->create(['status' => PostStatus::Draft, 'published_at' => null]);
    Post::factory()->for(User::factory(), 'author')->create(['status' => PostStatus::Published, 'published_at' => now()->addWeek()]);

    $response = $this->get('/');

    $response->assertOk()->assertSee($visible->title);
    expect(Post::published()->count())->toBe(1);
});

test('a category archive only lists published, due posts in that category', function () {
    $category = Category::factory()->create();
    $visible = Post::factory()->for(User::factory(), 'author')->for($category)->published()->create();
    Post::factory()->for(User::factory(), 'author')->for($category)->create(['status' => PostStatus::Draft, 'published_at' => null]);

    $response = $this->get("/categories/{$category->slug}");

    $response->assertOk()->assertSee($visible->title);
});

test('a tag archive only lists published, due posts with that tag', function () {
    $tag = Tag::factory()->create();

    $visible = Post::factory()->for(User::factory(), 'author')->published()->create();
    $visible->tags()->attach($tag);

    $hidden = Post::factory()->for(User::factory(), 'author')->create(['status' => PostStatus::Draft, 'published_at' => null]);
    $hidden->tags()->attach($tag);

    $response = $this->get("/tags/{$tag->slug}");

    $response->assertOk()->assertSee($visible->title)->assertDontSee($hidden->title);
});
