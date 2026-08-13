<?php

use App\Models\User;
use App\Models\Win;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('it seeds a minimum of three wins per day for Chase without duplication', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    $user = User::query()->where('email', 'chase@crumbls.com')->firstOrFail();

    $winsByDate = $user->wins()->get()
        ->groupBy(fn (Win $win): string => $win->created_at->toDateString());

    expect($user->wins()->count())->toBe(900)
        ->and($winsByDate)->toHaveCount(300)
        ->and($winsByDate->every(fn ($wins): bool => $wins->count() >= 3))->toBeTrue();
});
