<?php

use App\Actions\CreateWin;
use App\Mcp\Servers\WinsServer;
use App\Mcp\Tools\AddWin;
use App\Mcp\Tools\GetTodayStatus;
use App\Mcp\Tools\ListRecentWins;
use App\Mcp\Tools\SetDailyWinsGoal;
use App\Mcp\Tools\SetWeeklyCheckInDays;
use App\Models\User;
use App\Models\Win;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Laravel\Mcp\Request as McpRequest;
use Laravel\Mcp\Server\Registrar;
use Laravel\Passport\Client;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;

uses(LazilyRefreshDatabase::class);

test('MCP OAuth token lifetimes use Passport’s long-lived defaults', function () {
    expect(Passport::tokensExpireIn()->format('%y-%m-%d'))->toBe('1-0-0')
        ->and(Passport::refreshTokensExpireIn()->format('%y-%m-%d'))->toBe('1-0-0');
});

test('the MCP endpoint requires an authenticated Passport token with the MCP scope', function () {
    $this->postJson('/mcp/wins', [])->assertUnauthorized();

    $user = User::factory()->create();
    Passport::actingAs($user, []);

    $this->postJson('/mcp/wins', [])->assertForbidden();

    Passport::actingAs($user, [Registrar::OAUTH_SCOPE]);

    $this->postJson('/mcp/wins', [
        'jsonrpc' => '2.0',
        'id' => 'initialize-1',
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-11-25',
            'capabilities' => [],
            'clientInfo' => ['name' => 'Wins test', 'version' => '1.0.0'],
        ],
    ])->assertSuccessful()->assertJsonPath('result.serverInfo.name', 'Wins Server');
});

test('MCP requests are rate limited per authenticated user', function () {
    config(['mcp.max_requests_per_minute' => 1]);

    $user = User::factory()->create();
    Passport::actingAs($user, [Registrar::OAUTH_SCOPE]);

    $request = [
        'jsonrpc' => '2.0',
        'id' => 'initialize-1',
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-11-25',
            'capabilities' => [],
            'clientInfo' => ['name' => 'Wins test', 'version' => '1.0.0'],
        ],
    ];

    $this->postJson('/mcp/wins', $request)->assertSuccessful();
    $this->postJson('/mcp/wins', $request)
        ->assertTooManyRequests()
        ->assertHeader('Retry-After');
});

test('users fulfill Passport’s OAuth authenticatable contract', function () {
    expect(User::factory()->make())->toBeInstanceOf(OAuthenticatable::class);
});

test('MCP tools only use the authenticated user and share the website win limits', function () {
    $user = User::factory()->create([
        'daily_wins_goal' => 5,
        'timezone' => 'America/Denver',
    ]);
    config(['wins.max_per_day' => 2]);
    $otherUser = User::factory()->create();
    Win::factory()->for($otherUser)->create(['content' => 'Private win']);
    $this->actingAs($user);

    $created = app(AddWin::class)->handle(
        new McpRequest(['content' => '<strong>Finished the production MCP connection.</strong>']),
        app(CreateWin::class),
    );

    expect($created->getStructuredContent()['win']['content'])
        ->toBe('Finished the production MCP connection.')
        ->and($user->wins()->count())->toBe(1)
        ->and($otherUser->wins()->count())->toBe(1);

    $status = app(GetTodayStatus::class)->handle(new McpRequest);
    expect($status->getStructuredContent())->toMatchArray([
        'wins_today' => 1,
        'daily_limit' => 2,
        'remaining_wins' => 1,
        'daily_wins_goal' => 5,
        'remaining_goal_wins' => 4,
    ]);

    $feed = app(ListRecentWins::class)->handle(new McpRequest);
    expect($feed->getStructuredContent()['groups'][0]['wins'])
        ->toHaveCount(1)
        ->and($feed->getStructuredContent()['groups'][0]['wins'][0]['content'])
        ->toBe('Finished the production MCP connection.');
});

test('MCP win creation enforces the win content length boundaries', function () {
    $user = User::factory()->create();

    WinsServer::actingAs($user)
        ->tool(AddWin::class, ['content' => 'No'])
        ->assertHasErrors();

    expect($user->wins()->count())->toBe(0);
});

test('MCP users can set a daily reflection goal from one through five', function () {
    $user = User::factory()->create();

    WinsServer::actingAs($user)
        ->tool(SetDailyWinsGoal::class, ['daily_wins_goal' => 3])
        ->assertOk();

    expect($user->fresh()->daily_wins_goal)->toBe(3);

    WinsServer::actingAs($user)
        ->tool(SetDailyWinsGoal::class, ['daily_wins_goal' => 6])
        ->assertHasErrors();

    expect($user->fresh()->daily_wins_goal)->toBe(3);
});

test('MCP users can choose which days invite a reflection without blocking manual wins', function () {
    $this->travelTo('2026-08-15 12:00:00 UTC');

    $user = User::factory()->create(['timezone' => 'UTC']);
    $this->actingAs($user);

    $before = app(GetTodayStatus::class)->handle(new McpRequest);
    expect($before->getStructuredContent())->toMatchArray([
        'is_check_in_day' => false,
        'weekly_check_in_days' => User::DEFAULT_WEEKLY_CHECK_IN_DAYS,
    ]);

    $this->postJson(route('wins.store'), ['content' => 'Made time to rest.'])
        ->assertCreated();

    WinsServer::actingAs($user)
        ->tool(SetWeeklyCheckInDays::class, ['weekly_check_in_days' => [7, 6]])
        ->assertOk();

    $after = app(GetTodayStatus::class)->handle(new McpRequest);
    expect($user->fresh()->weekly_check_in_days)->toBe([6, 7])
        ->and($after->getStructuredContent())->toMatchArray([
            'is_check_in_day' => true,
            'weekly_check_in_days' => [6, 7],
        ]);
});

test('users can see and revoke their own MCP connection', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['name' => 'Codex']);
    $token = Token::query()->create([
        'id' => 'mcp-token-1',
        'user_id' => $user->id,
        'client_id' => $client->id,
        'name' => null,
        'scopes' => [Registrar::OAUTH_SCOPE],
        'revoked' => false,
        'expires_at' => now()->addHour(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertViewHas('hasMcpConnection', true);

    $this->actingAs($user)
        ->get(route('connections.index'))
        ->assertSuccessful()
        ->assertViewIs('connections.index')
        ->assertViewHas('connections');

    $this->delete(route('connections.destroy', $client->id))
        ->assertRedirect(route('connections.index'));

    expect($token->fresh()->revoked)->toBeTrue();
});
