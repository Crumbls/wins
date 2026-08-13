<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('guests are redirected to the login screen', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('users can register with their detected timezone', function () {
    $this->post(route('register.store'), [
        'name' => 'Taylor Otwell',
        'email' => 'taylor@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'timezone' => 'America/Denver',
    ])->assertRedirect(route('dashboard'));

    $user = User::query()->where('email', 'taylor@example.com')->firstOrFail();

    $this->assertAuthenticatedAs($user);
    expect($user->timezone)->toBe('America/Denver')
        ->and($user->daily_wins_goal)->toBe(5)
        ->and($user->weekly_check_in_days)->toBe(User::DEFAULT_WEEKLY_CHECK_IN_DAYS)
        ->and($user->wins_timeline_view)->toBe('daily');
});

test('users must provide a valid timezone when registering', function () {
    $this->post(route('register.store'), [
        'name' => 'Taylor Otwell',
        'email' => 'taylor@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertSessionHasErrors('timezone');
});

test('authenticated users can update their timezone', function () {
    $user = User::factory()->create(['timezone' => 'UTC']);

    $this->actingAs($user)
        ->post(route('timezone.update'), ['timezone' => 'America/New_York'])
        ->assertNoContent();

    expect($user->fresh()->timezone)->toBe('America/New_York');
});

test('authenticated users can save their timeline view preference', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->putJson(route('timeline-view.update'), ['view' => 'weekly'])
        ->assertNoContent();

    expect($user->fresh()->wins_timeline_view)->toBe('weekly');

    $this->actingAs($user)
        ->putJson(route('timeline-view.update'), ['view' => 'monthly'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('view');
});

test('users can log in with valid credentials', function () {
    $user = User::factory()->create(['password' => 'password']);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});
