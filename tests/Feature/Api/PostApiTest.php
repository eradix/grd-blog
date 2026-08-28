<?php

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

test('the posts index only returns published, due posts', function () {
    $visible = Post::factory()->for(User::factory(), 'author')->published()->create();
    Post::factory()->for(User::factory(), 'author')->create(['status' => PostStatus::Draft, 'published_at' => null]);
    Post::factory()->for(User::factory(), 'author')->create(['status' => PostStatus::Published, 'published_at' => now()->addWeek()]);

    $response = $this->getJson('/api/v1/posts');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.slug'))->toBe($visible->slug);
});

test('the posts index response has the documented summary shape', function () {
    $category = Category::factory()->create();
    $tag = Tag::factory()->create();
    $post = Post::factory()->for(User::factory(), 'author')->for($category)->published()->create();
    $post->tags()->attach($tag);

    $this->getJson('/api/v1/posts')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                ['id', 'title', 'slug', 'excerpt', 'published_at', 'author' => ['id', 'name'], 'category' => ['id', 'name', 'slug'], 'tags', 'comments_count'],
            ],
        ])
        ->assertJsonMissingPath('data.0.body_html');
});

test('a draft post 404s on the show endpoint', function () {
    $post = Post::factory()->for(User::factory(), 'author')->create(['status' => PostStatus::Draft, 'published_at' => null]);

    $this->getJson("/api/v1/posts/{$post->slug}")->assertNotFound();
});

test('a future-dated published post 404s on the show endpoint', function () {
    $post = Post::factory()->for(User::factory(), 'author')->create([
        'status' => PostStatus::Published,
        'published_at' => now()->addDay(),
    ]);

    $this->getJson("/api/v1/posts/{$post->slug}")->assertNotFound();
});

test('a published post is returned with its rendered body on the show endpoint', function () {
    $post = Post::factory()->for(User::factory(), 'author')->published()->create([
        'body' => "# Heading\n\nSome text.",
    ]);

    $this->getJson("/api/v1/posts/{$post->slug}")
        ->assertOk()
        ->assertJsonPath('data.slug', $post->slug)
        ->assertJsonPath('data.body_html', $post->fresh()->body_html);
});
