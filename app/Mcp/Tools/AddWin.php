<?php

namespace App\Mcp\Tools;

use App\Actions\CreateWin;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Records one explicitly stated win for the signed-in user. This is a write action and follows the same daily limit as the Wins website.')]
class AddWin extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request, CreateWin $createWin): Response|ResponseFactory
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return Response::error('Authentication is required to record a win.');
        }

        $validated = $request->validate(['content' => ['required', 'string', 'max:1000']]);
        $win = $createWin->handle($user, $validated['content']);

        return Response::structured(['win' => [
            'id' => $win->id,
            'content' => $win->content,
            'created_at' => $win->created_at->toIso8601String(),
        ]]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return ['content' => $schema->string()->description('A concise reflection on something that went well.')->required()];
    }
}
