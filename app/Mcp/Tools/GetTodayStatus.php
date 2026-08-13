<?php

namespace App\Mcp\Tools;

use App\Models\User;
use App\Models\Win;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Gets today’s date, recorded win count, server-enforced capacity, personal reflection goal, and current streak for the signed-in user.')]
class GetTodayStatus extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return Response::error('Authentication is required to read wins.');
        }

        $now = now('UTC')->setTimezone($user->timezone);
        $winsToday = $user->wins()->whereBetween('created_at', [$now->copy()->startOfDay()->utc(), $now->copy()->endOfDay()->utc()])->count();
        $dailyLimit = max(1, (int) config('wins.max_per_day'));
        $dailyWinsGoal = max(1, $user->daily_wins_goal);

        return Response::structured([
            'date' => $now->toDateString(),
            'wins_today' => $winsToday,
            'daily_limit' => $dailyLimit,
            'remaining_wins' => max(0, $dailyLimit - $winsToday),
            'daily_wins_goal' => $dailyWinsGoal,
            'remaining_goal_wins' => max(0, $dailyWinsGoal - $winsToday),
            'is_check_in_day' => $user->isCheckInDay($now->dayOfWeekIso),
            'weekly_check_in_days' => $user->weeklyCheckInDays(),
            'current_streak_days' => Win::currentDailyStreakFor($user),
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
