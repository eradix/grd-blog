<?php

use App\Enums\Role;
use App\Models\Post;
use App\Models\User;

test('an author can update and delete their own post', function () {
    $author = User::factory()->role(Role::Author)->create();
    $post = Post::factory()->for($author, 'author')->create();

    expect($author->can('update', $post))->toBeTrue();
    expect($author->can('delete', $post))->toBeTrue();
});

test('an author cannot update or delete another author\'s post', function () {
    $author = User::factory()->role(Role::Author)->create();
    $otherAuthor = User::factory()->role(Role::Author)->create();
    $post = Post::factory()->for($otherAuthor, 'author')->create();

    expect($author->can('update', $post))->toBeFalse();
    expect($author->can('delete', $post))->toBeFalse();
});

test('admins and editors can update and delete any post', function () {
    $post = Post::factory()->for(User::factory(), 'author')->create();

    foreach ([Role::Admin, Role::Editor] as $role) {
        $staff = User::factory()->role($role)->create();

        expect($staff->can('update', $post))->toBeTrue();
        expect($staff->can('delete', $post))->toBeTrue();
    }
});

test('a reader cannot create posts', function () {
    $reader = User::factory()->role(Role::Reader)->create();

    expect($reader->can('create', Post::class))->toBeFalse();
});
