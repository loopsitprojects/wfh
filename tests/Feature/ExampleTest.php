<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the application redirects to login when guest tries to access root', function () {
    $response = $this->get('/');
    $response->assertStatus(302);
    $response->assertRedirect(route('login'));
});

test('login is case-insensitive for username', function () {
    // Create user with mixed case
    $user = User::create([
        'name' => 'John Doe',
        'username' => 'JohnDoe',
        'email' => 'john@example.com',
        'password' => bcrypt('password123'),
        'role' => 'employee',
        'is_active' => true,
    ]);

    // Attempt login with lowercase username
    $response = $this->post('/login', [
        'username' => 'johndoe',
        'password' => 'password123',
    ]);

    $response->assertRedirect(route('employee.dashboard'));
    $this->assertAuthenticatedAs($user);
});

