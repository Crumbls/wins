<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Win;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $now = now('UTC')->setTimezone($user->timezone);
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();

        $weeklyWins = $user->wins()
            ->whereBetween('created_at', [$weekStart->copy()->utc(), $weekEnd->copy()->utc()])
            ->latest()
            ->get();

        $weekDays = collect(range(0, 6))->map(function (int $offset) use ($user, $weekStart, $weeklyWins): array {
            $date = $weekStart->copy()->addDays($offset);

            return [
                'date' => $date,
                'wins' => $weeklyWins->filter(
                    fn (Win $win): bool => $win->created_at->copy()->setTimezone($user->timezone)->isSameDay($date),
                )->values(),
            ];
        });

        $recentWinFeed = Win::dateGroupsFor($user);

        return view('dashboard', [
            'hasMcpConnection' => $user->connections()->exists(),
            'nextRecentWinsBefore' => $recentWinFeed['nextBefore'],
            'recentWins' => $recentWinFeed['groups'],
            'timelineView' => $user->wins_timeline_view,
            'weekDays' => $weekDays,
            'weeklyWinsCount' => $weeklyWins->count(),
            'user' => $user,
        ]);
    }
}
