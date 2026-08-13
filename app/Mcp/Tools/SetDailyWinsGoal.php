<?php

namespace App\Mcp\Tools;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Updates the signed-in user’s daily reflection goal. The value must be between 1 and 5 and does not change the server-enforced daily win limit.')]
class SetDailyWinsGoal extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return Response::error('Authentication is required to update the daily reflection goal.');
        }

        $validated = $request->validate([
            'daily_wins_goal' => ['required', 'integer', 'between:1,5'],
        ], [
            'daily_wins_goal.between' => 'Choose a daily reflection goal between 1 and 5.',
        ]);

        $user->update(['daily_wins_goal' => $validated['daily_wins_goal']]);

        return Response::structured(['daily_wins_goal' => $user->daily_wins_goal]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'daily_wins_goal' => $schema->integer()
                ->min(1)
                ->max(5)
                ->description('The number of wins an agent should use as a gentle daily reflection target, from 1 to 5. This does not change the server-enforced daily limit.')
                ->required(),
        ];
    }
}
