<?php

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

test('the categories index lists all categories', function () {
    Category::factory()->count(3)->create();

    $this->getJson('/api/v1/categories')->assertOk()->assertJsonCount(3, 'data');
});

test('a category show endpoint only lists published, due posts in that category', function () {
    $category = Category::factory()->create();
    $visible = Post::factory()->for(User::factory(), 'author')->for($category)->published()->create();
    Post::factory()->for(User::factory(), 'author')->for($category)->create(['status' => PostStatus::Draft, 'published_at' => null]);

    $response = $this->getJson("/api/v1/categories/{$category->slug}");

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.slug'))->toBe($visible->slug);
    expect($response->json('category.slug'))->toBe($category->slug);
});

test('the tags index lists all tags', function () {
    Tag::factory()->count(4)->create();

    $this->getJson('/api/v1/tags')->assertOk()->assertJsonCount(4, 'data');
});

test('a tag show endpoint only lists published, due posts with that tag', function () {
    $tag = Tag::factory()->create();

    $visible = Post::factory()->for(User::factory(), 'author')->published()->create();
    $visible->tags()->attach($tag);

    $hidden = Post::factory()->for(User::factory(), 'author')->create(['status' => PostStatus::Draft, 'published_at' => null]);
    $hidden->tags()->attach($tag);

    $response = $this->getJson("/api/v1/tags/{$tag->slug}");

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.slug'))->toBe($visible->slug);
});

test('search only returns published, due posts matching the query', function () {
    $match = Post::factory()->for(User::factory(), 'author')->published()->create(['title' => 'A Guide To Laravel Testing']);
    Post::factory()->for(User::factory(), 'author')->published()->create(['title' => 'Something Unrelated']);

    $response = $this->getJson('/api/v1/search?q=Laravel');

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.slug'))->toBe($match->slug);
});
