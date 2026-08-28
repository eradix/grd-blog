<?php

use App\Models\User;

test('a user can obtain a token with valid credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('correct-password')]);

    $response = $this->postJson('/api/v1/auth/tokens', [
        'email' => $user->email,
        'password' => 'correct-password',
        'device_name' => 'test-suite',
    ]);

    $response->assertCreated()->assertJsonStructure(['token', 'token_type']);
    expect($user->tokens()->count())->toBe(1);
});

test('a token request with the wrong password is rejected', function () {
    $user = User::factory()->create(['password' => bcrypt('correct-password')]);

    $this->postJson('/api/v1/auth/tokens', [
        'email' => $user->email,
        'password' => 'wrong-password',
        'device_name' => 'test-suite',
    ])->assertUnprocessable()->assertJsonValidationErrors('email');

    expect($user->tokens()->count())->toBe(0);
});

test('token issuance is rate limited', function () {
    $user = User::factory()->create(['password' => bcrypt('correct-password')]);

    for ($i = 0; $i < 5; $i++) {
        $this->postJson('/api/v1/auth/tokens', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'device_name' => 'test-suite',
        ])->assertUnprocessable();
    }

    $this->postJson('/api/v1/auth/tokens', [
        'email' => $user->email,
        'password' => 'correct-password',
        'device_name' => 'test-suite',
    ])->assertStatus(429);
});

test('a token can be revoked and no longer authenticates', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-suite')->plainTextToken;

    $this->withHeader('Authorization', "Bearer {$token}")
        ->deleteJson('/api/v1/auth/tokens')
        ->assertNoContent();

    $this->withHeader('Authorization', "Bearer {$token}")
        ->getJson('/api/v1/posts')
        ->assertOk(); // reading posts never required a token

    expect($user->tokens()->count())->toBe(0);
});
