<?php

use App\Enums\Role;
use App\Models\User;

test('a reader is forbidden from the admin panel even with a valid session', function () {
    $reader = User::factory()->role(Role::Reader)->create();

    $this->actingAs($reader)->get('/admin')->assertForbidden();
});

test('an unverified staff member is forbidden from the admin panel', function () {
    $author = User::factory()->role(Role::Author)->unverified()->create();

    $this->actingAs($author)->get('/admin')->assertForbidden();
});

test('a verified admin, editor, and author can reach the admin panel', function () {
    foreach ([Role::Admin, Role::Editor, Role::Author] as $role) {
        $user = User::factory()->role($role)->create();

        $this->actingAs($user)->get('/admin')->assertOk();
    }
});
