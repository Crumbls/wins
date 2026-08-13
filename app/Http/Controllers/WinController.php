<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateWin;
use App\Http\Requests\StoreWinRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class WinController extends Controller
{
    public function store(StoreWinRequest $request, CreateWin $createWin): JsonResponse|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $win = $createWin->handle($user, $request->validated('content'));

        if ($request->expectsJson()) {
            return response()->json([
                'win' => [
                    'id' => $win->id,
                    'content' => $win->content,
                    'created_at' => $win->created_at->toIso8601String(),
                ],
            ], 201);
        }

        return redirect()->route('dashboard');
    }
}
