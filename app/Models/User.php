<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Mcp\Server\Registrar;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'timezone', 'daily_wins_goal', 'weekly_check_in_days', 'wins_timeline_view'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements OAuthenticatable
{
    public const DEFAULT_WEEKLY_CHECK_IN_DAYS = [1, 2, 3, 4, 5];

    /** @use HasFactory<UserFactory> */
    use HasApiTokens,
        HasFactory,
        Notifiable;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'timezone' => 'UTC',
        'daily_wins_goal' => 5,
        'weekly_check_in_days' => '[1,2,3,4,5]',
        'wins_timeline_view' => 'daily',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'daily_wins_goal' => 'integer',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'weekly_check_in_days' => 'array',
        ];
    }

    /**
     * @return HasMany<Win, $this>
     */
    public function wins(): HasMany
    {
        return $this->hasMany(Win::class);
    }

    public function isCheckInDay(int $dayOfWeekIso): bool
    {
        return in_array($dayOfWeekIso, $this->weeklyCheckInDays(), true);
    }

    /**
     * @return array<int, int>
     */
    public function weeklyCheckInDays(): array
    {
        return $this->weekly_check_in_days ?? self::DEFAULT_WEEKLY_CHECK_IN_DAYS;
    }

    public function connections(): HasMany
    {
        return $this
            ->tokens()
            ->with('client')
            ->whereJsonContains('scopes', Registrar::OAUTH_SCOPE)
            ->where('revoked', false)
            ->where('expires_at', '>', now());
    }
}
