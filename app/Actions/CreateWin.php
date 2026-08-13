<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Models\Win;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateWin
{
    public function handle(User $authenticatedUser, string $content): Win
    {
        return DB::transaction(function () use ($authenticatedUser, $content): Win {
            $user = User::query()->lockForUpdate()->findOrFail($authenticatedUser->id);
            $now = now('UTC')->setTimezone($user->timezone);
            $maximumWinsPerDay = max(1, (int) config('wins.max_per_day'));
            $winsToday = $user->wins()->whereBetween('created_at', [$now->copy()->startOfDay()->utc(), $now->copy()->endOfDay()->utc()])->count();

            if ($winsToday >= $maximumWinsPerDay) {
                throw ValidationException::withMessages(['content' => 'You have reached your daily limit of '.number_format($maximumWinsPerDay).' wins.']);
            }

            return $user->wins()->create(['content' => $content]);
        });
    }
}
