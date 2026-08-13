<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListWinsRequest;
use App\Models\Win;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class WinFeedController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ListWinsRequest $request): JsonResponse
    {
        $user = $request->user();
        $before = $request->validated('before');
        $feed = Win::dateGroupsFor($user, $before);

        $groups = $feed['groups']
            ->map(fn (Collection $wins, string $date): array => [
                'date' => $date,
                'wins' => $wins->map(fn (Win $win): array => [
                    'id' => $win->id,
                    'content' => $win->content,
                    'created_at' => $win->created_at->toIso8601String(),
                ])->values(),
            ])
            ->values();

        return response()->json([
            'groups' => $groups,
            'next_before' => $feed['nextBefore'],
        ]);
    }
}
