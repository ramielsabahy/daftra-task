<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('user can login with valid credentials', function () {
    $password = 'password123';
    $user = User::factory()->create([
        'password' => Hash::make($password),
    ]);

    $response = $this->postJson(route('auth.login'), [
        'email' => $user->email,
        'password' => $password,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user',
                'token',
            ],
        ]);
});

test('user cannot login with invalid credentials', function () {
    $user = User::factory()->create();

    $response = $this->postJson(route('auth.login'), [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401)
        ->assertJson([
            'success' => false,
            'message' => 'Invalid credentials',
        ]);
});

test('authenticated user can get their profile', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('auth.me'));

    $response->assertStatus(200)
        ->assertJsonPath('data.email', $user->email);
});

test('authenticated user can logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withToken($token)->postJson(route('auth.logout'));

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);

    expect($user->tokens()->count())->toBe(0);
});
