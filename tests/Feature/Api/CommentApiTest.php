<?php

use App\Enums\CommentStatus;
use App\Enums\Role;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

test('the comments endpoint only returns approved comments', function () {
    $post = Post::factory()->for(User::factory(), 'author')->published()->create();
    $approved = Comment::factory()->for($post)->for(User::factory(), 'author')->approved()->create();
    Comment::factory()->for($post)->for(User::factory(), 'author')->create(['status' => CommentStatus::Pending]);

    $response = $this->getJson("/api/v1/posts/{$post->slug}/comments");

    $response->assertOk()->assertJsonCount(1, 'data');
    expect($response->json('data.0.id'))->toBe($approved->id);
});

test('a guest cannot post a comment via the api', function () {
    $post = Post::factory()->for(User::factory(), 'author')->published()->create();

    $this->postJson("/api/v1/posts/{$post->slug}/comments", ['body' => 'hello'])
        ->assertUnauthorized();

    expect(Comment::count())->toBe(0);
});

test('an unverified user cannot post a comment via the api', function () {
    $post = Post::factory()->for(User::factory(), 'author')->published()->create();
    $user = User::factory()->role(Role::Reader)->unverified()->create();

    $this->actingAs($user)
        ->postJson("/api/v1/posts/{$post->slug}/comments", ['body' => 'hello'])
        ->assertForbidden();
});

test('a verified user\'s comment via the api is stored pending and hidden until approved', function () {
    $post = Post::factory()->for(User::factory(), 'author')->published()->create();
    $user = User::factory()->role(Role::Reader)->create();

    $response = $this->actingAs($user)
        ->postJson("/api/v1/posts/{$post->slug}/comments", ['body' => 'Nice write-up!']);

    $response->assertCreated()->assertJsonPath('data.status', CommentStatus::Pending->value);

    $comment = Comment::sole();
    expect($comment->status)->toBe(CommentStatus::Pending)
        ->and($comment->user_id)->toBe($user->id);

    $this->getJson("/api/v1/posts/{$post->slug}/comments")->assertJsonCount(0, 'data');
});

test('an empty comment body is rejected', function () {
    $post = Post::factory()->for(User::factory(), 'author')->published()->create();
    $user = User::factory()->role(Role::Reader)->create();

    $this->actingAs($user)
        ->postJson("/api/v1/posts/{$post->slug}/comments", ['body' => ''])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('body');
});

test('comment posting via the api is rate limited', function () {
    $post = Post::factory()->for(User::factory(), 'author')->published()->create();
    $user = User::factory()->role(Role::Reader)->create();

    $this->actingAs($user);

    for ($i = 0; $i < 5; $i++) {
        $this->postJson("/api/v1/posts/{$post->slug}/comments", ['body' => "comment {$i}"])
            ->assertCreated();
    }

    $this->postJson("/api/v1/posts/{$post->slug}/comments", ['body' => 'one too many'])
        ->assertStatus(429);
});
