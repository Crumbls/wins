<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WinFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

#[Fillable(['content'])]
class Win extends Model
{
    private const STREAK_CACHE_TTL_SECONDS = 60;

    /** @use HasFactory<WinFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::created(fn (Win $win) => self::forgetStreakCacheForUser($win->user_id));

        static::updated(function (Win $win): void {
            self::forgetStreakCacheForUser($win->user_id);

            if ($win->wasChanged('user_id')) {
                self::forgetStreakCacheForUser($win->getOriginal('user_id'));
            }
        });

        static::deleted(fn (Win $win) => self::forgetStreakCacheForUser($win->user_id));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array<int, string>
     */
    public static function contentRules(): array
    {
        return ['required', 'string', 'min:3', 'max:1000'];
    }

    public static function sanitizeContent(string $content): string
    {
        return trim(strip_tags($content));
    }

    public static function currentDailyStreakFor(User $user): int
    {
        return self::currentStreakFor($user, weekdaysOnly: false);
    }

    public static function currentWeekdayStreakFor(User $user): int
    {
        return self::currentStreakFor($user, weekdaysOnly: true);
    }

    /**
     * @return array{groups: Collection<string, Collection<int, Win>>, nextBefore: ?string}
     */
    public static function dateGroupsFor(User $user, ?string $before = null, int $limit = 7): array
    {
        $beforeAt = $before === null
            ? null
            : Carbon::createFromFormat('Y-m-d', $before, $user->timezone)->startOfDay()->utc();
        $groups = collect();

        foreach ($user->wins()
            ->select(['id', 'user_id', 'content', 'created_at'])
            ->when($beforeAt, fn ($query) => $query->where('created_at', '<', $beforeAt))
            ->latest()
            ->orderByDesc('id')
            ->cursor() as $win) {
            $date = $win->created_at->copy()->setTimezone($user->timezone)->toDateString();

            if (! $groups->has($date) && $groups->count() === $limit) {
                break;
            }

            $groups->put($date, $groups->get($date, collect())->push($win));
        }

        return [
            'groups' => $groups,
            'nextBefore' => $groups->keys()->last(),
        ];
    }

    private static function currentStreakFor(User $user, bool $weekdaysOnly): int
    {
        return Cache::remember(
            self::streakCacheKey($user->getKey(), $weekdaysOnly),
            self::STREAK_CACHE_TTL_SECONDS,
            fn (): int => self::calculateCurrentStreakFor($user, $weekdaysOnly),
        );
    }

    private static function calculateCurrentStreakFor(User $user, bool $weekdaysOnly): int
    {
        $expectedDate = now('UTC')->setTimezone($user->timezone)->startOfDay();

        while ($weekdaysOnly && $expectedDate->isWeekend()) {
            $expectedDate->subDay();
        }

        $streakDays = 0;

        foreach ($user->wins()
            ->select(['id', 'user_id', 'created_at'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursor() as $win) {
            $winDate = $win->created_at->copy()->setTimezone($user->timezone)->startOfDay();

            if ($winDate->isAfter($expectedDate)) {
                continue;
            }

            if ($winDate->isBefore($expectedDate)) {
                break;
            }

            $streakDays++;
            $expectedDate->subDay();

            while ($weekdaysOnly && $expectedDate->isWeekend()) {
                $expectedDate->subDay();
            }
        }

        return $streakDays;
    }

    private static function forgetStreakCacheForUser(int|string|null $userId): void
    {
        if ($userId === null) {
            return;
        }

        Cache::forget(self::streakCacheKey($userId, weekdaysOnly: false));
        Cache::forget(self::streakCacheKey($userId, weekdaysOnly: true));
    }

    private static function streakCacheKey(int|string $userId, bool $weekdaysOnly): string
    {
        return 'wins:streak:'.$userId.':'.($weekdaysOnly ? 'weekdays' : 'daily');
    }
}
