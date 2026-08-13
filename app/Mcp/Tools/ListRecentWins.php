<?php

namespace App\Mcp\Tools;

use App\Models\User;
use App\Models\Win;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Collection;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Lists recent wins, grouped by the signed-in user’s displayed date. Use the next_before value to continue earlier in the journal.')]
class ListRecentWins extends Tool
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

        $validated = $request->validate([
            'before' => ['nullable', 'date_format:Y-m-d'],
            'date_groups' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);
        $feed = Win::dateGroupsFor($user, $validated['before'] ?? null, $validated['date_groups'] ?? 7);

        return Response::structured([
            'groups' => $feed['groups']->map(fn (Collection $wins, string $date): array => [
                'date' => $date,
                'wins' => $wins->map(fn (Win $win): array => [
                    'id' => $win->id,
                    'content' => $win->content,
                    'created_at' => $win->created_at->toIso8601String(),
                ])->values()->all(),
            ])->values()->all(),
            'next_before' => $feed['nextBefore'],
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'before' => $schema->string()->description('Load only dates before this ISO date (YYYY-MM-DD).')->nullable(),
            'date_groups' => $schema->integer()->description('Number of date groups to return, from 1 to 30.')->nullable(),
        ];
    }
}
