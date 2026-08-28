<?php

use App\Enums\CommentStatus;
use App\Enums\Role;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

test('a guest cannot submit a comment', function () {
    $post = Post::factory()->for(User::factory(), 'author')->published()->create();

    Livewire::test('comments', ['post' => $post])
        ->call('submit')
        ->assertStatus(403);

    expect(Comment::count())->toBe(0);
});

test('a verified user\'s comment is stored pending and is not publicly visible until approved', function () {
    $post = Post::factory()->for(User::factory(), 'author')->published()->create();
    $reader = User::factory()->role(Role::Reader)->create();

    Livewire::actingAs($reader)
        ->test('comments', ['post' => $post])
        ->set('body', 'This is a great post!')
        ->call('submit');

    $comment = Comment::sole();
    expect($comment->status)->toBe(CommentStatus::Pending);

    $this->get("/posts/{$post->slug}")->assertDontSee('This is a great post!');

    $comment->update(['status' => CommentStatus::Approved]);

    $this->get("/posts/{$post->slug}")->assertSee('This is a great post!');
});
