<?php

namespace App\Mcp\Tools;

use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Updates the days when an agent may initiate a reflection. Use ISO weekday numbers: 1 is Monday and 7 is Sunday. An empty list turns off agent-initiated check-ins for the week.')]
class SetWeeklyCheckInDays extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return Response::error('Authentication is required to update the weekly check-in days.');
        }

        $validated = $request->validate([
            'weekly_check_in_days' => ['present', 'array'],
            'weekly_check_in_days.*' => ['integer', 'between:1,7', 'distinct'],
        ], [
            'weekly_check_in_days.*.between' => 'Check-in days use ISO weekday numbers from 1 (Monday) through 7 (Sunday).',
        ]);
        $checkInDays = collect($validated['weekly_check_in_days'])
            ->map(fn (int $day): int => $day)
            ->sort()
            ->values()
            ->all();

        $user->update(['weekly_check_in_days' => $checkInDays]);

        return Response::structured(['weekly_check_in_days' => $user->weeklyCheckInDays()]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'weekly_check_in_days' => $schema->array()
                ->items($schema->integer()->min(1)->max(7))
                ->max(7)
                ->unique()
                ->description('ISO weekdays when an agent may initiate a check-in: 1 is Monday and 7 is Sunday. An empty array disables check-ins.')
                ->required(),
        ];
    }
}
