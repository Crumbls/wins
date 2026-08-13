<?php

use App\Models\User;
use App\Models\Win;
use App\View\Components\StatusTicker;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('authenticated users log wins with a UTC created timestamp', function () {
    $user = User::factory()->create(['timezone' => 'America/Denver']);

    $this->travelTo('2026-08-13 00:30:00 UTC');

    $this->actingAs($user)
        ->post(route('wins.store'), ['content' => 'Finished the first version of the journal.'])
        ->assertRedirect(route('dashboard'));

    $win = Win::query()->firstOrFail();

    expect($win->content)->toBe('Finished the first version of the journal.')
        ->and($win->created_at->utc()->toDateTimeString())->toBe('2026-08-13 00:30:00');
});

test('wins cannot be created by guests', function () {
    $this->post(route('wins.store'), ['content' => 'A private win.'])
        ->assertRedirect(route('login'));
});

test('wins created through the asynchronous form return their created timestamp', function () {
    $user = User::factory()->create(['timezone' => 'America/Denver']);

    $this->travelTo('2026-08-13 00:30:00 UTC');

    $this->actingAs($user)
        ->postJson(route('wins.store'), ['content' => 'Completed the Alpine submission flow.'])
        ->assertCreated()
        ->assertJsonPath('win.content', 'Completed the Alpine submission flow.')
        ->assertJsonStructure(['win' => ['id', 'content', 'created_at']])
        ->assertJsonMissingPath('win.local_date');
});

test('wins store plain text when submitted with HTML', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('wins.store'), ['content' => '<strong>Finished the difficult thing.</strong>'])
        ->assertCreated()
        ->assertJsonPath('win.content', 'Finished the difficult thing.');

    expect(Win::query()->sole()->content)->toBe('Finished the difficult thing.');
});

test('wins must be between three and one thousand characters', function (string $content) {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('wins.store'), ['content' => $content])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('content');
})->with([
    'too short after HTML is removed' => '<strong>No</strong>',
    'too long' => str_repeat('a', 1001),
]);

test('win submissions are rate limited per authenticated user', function () {
    config(['wins.max_per_day' => 13]);

    $user = User::factory()->create();

    $this->actingAs($user);

    foreach (range(1, 12) as $attempt) {
        $this->postJson(route('wins.store'), ['content' => "Win {$attempt}"])
            ->assertCreated();
    }

    $this->postJson(route('wins.store'), ['content' => 'One win too many.'])
        ->assertTooManyRequests()
        ->assertHeader('Retry-After')
        ->assertJsonPath('message', 'You are logging wins too quickly. Please wait a moment before trying again.');
});

test('win submissions stop at the configured daily maximum', function () {
    $user = User::factory()->create([
        'daily_wins_goal' => 5,
        'timezone' => 'America/Denver',
    ]);
    config(['wins.max_per_day' => 2]);

    $this->travelTo('2026-08-13 18:00:00 UTC');

    Win::factory()->count(2)->for($user)->create([
        'created_at' => '2026-08-13 12:00:00 UTC',
        'updated_at' => '2026-08-13 12:00:00 UTC',
    ]);

    $this->actingAs($user)
        ->postJson(route('wins.store'), ['content' => 'One more win.'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('content')
        ->assertJsonPath('errors.content.0', 'You have reached your daily limit of 2 wins.');

    expect($user->wins()->count())->toBe(2);
});

test('authenticated users can load earlier complete date groups of their wins', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    Win::factory()->create([
        'user_id' => $user,
        'content' => 'Newest win',
        'created_at' => '2026-08-13 12:00:00 UTC',
        'updated_at' => '2026-08-13 12:00:00 UTC',
    ]);
    Win::factory()->create([
        'user_id' => $user,
        'content' => 'First older win',
        'created_at' => '2026-08-10 12:00:00 UTC',
        'updated_at' => '2026-08-10 12:00:00 UTC',
    ]);
    Win::factory()->create([
        'user_id' => $user,
        'content' => 'Second older win',
        'created_at' => '2026-08-10 10:00:00 UTC',
        'updated_at' => '2026-08-10 10:00:00 UTC',
    ]);
    Win::factory()->create([
        'user_id' => $otherUser,
        'content' => 'Private win',
        'created_at' => '2026-08-10 12:00:00 UTC',
        'updated_at' => '2026-08-10 12:00:00 UTC',
    ]);

    $this->actingAs($user)
        ->getJson(route('wins.feed', ['before' => '2026-08-13']))
        ->assertSuccessful()
        ->assertJsonPath('groups.0.date', '2026-08-10')
        ->assertJsonCount(2, 'groups.0.wins')
        ->assertJsonMissing(['content' => 'Private win'])
        ->assertJsonPath('next_before', '2026-08-10');
});

test('the dashboard receives the user’s weekly overview data and timeline preference', function () {
    $user = User::factory()->create(['wins_timeline_view' => 'weekly']);
    Win::factory()->for($user)->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertViewIs('dashboard')
        ->assertViewHas('timelineView', 'weekly')
        ->assertViewHas('weeklyWinsCount', 1)
        ->assertViewHas('weekDays', fn ($weekDays): bool => $weekDays->count() === 7);
});

test('daily streaks count consecutive days with wins', function () {
    $user = User::factory()->create(['timezone' => 'America/Denver']);

    $this->travelTo('2026-08-13 18:00:00 UTC');

    foreach (['2026-08-13', '2026-08-12', '2026-08-11'] as $date) {
        Win::factory()->for($user)->create([
            'created_at' => "{$date} 12:00:00 UTC",
            'updated_at' => "{$date} 12:00:00 UTC",
        ]);
    }

    expect(Win::currentDailyStreakFor($user))->toBe(3);
});

test('win changes invalidate a user’s cached streak', function () {
    $user = User::factory()->create(['timezone' => 'America/Denver']);

    $this->travelTo('2026-08-13 18:00:00 UTC');

    $today = Win::factory()->for($user)->create([
        'created_at' => '2026-08-13 12:00:00 UTC',
        'updated_at' => '2026-08-13 12:00:00 UTC',
    ]);

    expect(Win::currentDailyStreakFor($user))->toBe(1);

    $yesterday = Win::factory()->for($user)->create([
        'created_at' => '2026-08-12 12:00:00 UTC',
        'updated_at' => '2026-08-12 12:00:00 UTC',
    ]);

    expect(Win::currentDailyStreakFor($user))->toBe(2);

    $yesterday->forceFill([
        'created_at' => '2026-08-11 12:00:00 UTC',
        'updated_at' => '2026-08-11 12:00:00 UTC',
    ])->save();

    expect(Win::currentDailyStreakFor($user))->toBe(1);

    $today->delete();

    expect(Win::currentDailyStreakFor($user))->toBe(0);
});

test('the status ticker resolves the current user streak when one is not supplied', function () {
    $user = User::factory()->create(['timezone' => 'America/Denver']);

    $this->travelTo('2026-08-13 18:00:00 UTC');

    foreach (['2026-08-13', '2026-08-12'] as $date) {
        Win::factory()->for($user)->create([
            'created_at' => "{$date} 12:00:00 UTC",
            'updated_at' => "{$date} 12:00:00 UTC",
        ]);
    }

    $this->actingAs($user);

    $ticker = new StatusTicker;

    expect($ticker->date->getTimezone()->getName())
        ->toBe('America/Denver')
        ->and($ticker->streakDays)
        ->toBe(2)
        ->and((new StatusTicker(streakDays: 8))->streakDays)
        ->toBe(8);
});

test('weekday streaks skip Saturday and Sunday', function () {
    $user = User::factory()->create(['timezone' => 'America/Denver']);

    $this->travelTo('2026-08-17 18:00:00 UTC');

    foreach (['2026-08-17', '2026-08-14', '2026-08-13'] as $date) {
        Win::factory()->for($user)->create([
            'created_at' => "{$date} 12:00:00 UTC",
            'updated_at' => "{$date} 12:00:00 UTC",
        ]);
    }

    expect(Win::currentWeekdayStreakFor($user))->toBe(3);
});
