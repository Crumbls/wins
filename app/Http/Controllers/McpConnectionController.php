<?php

namespace App\Http\Controllers;

use App\Http\Requests\RevokeMcpConnectionRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Mcp\Server\Registrar;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;

class McpConnectionController extends Controller
{
    public function index(Request $request): View
    {
        $connections = $request->user()->tokens()
            ->with('client')
            ->whereJsonContains('scopes', Registrar::OAUTH_SCOPE)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->get()
            ->groupBy('client_id')
            ->map(fn ($tokens) => [
                'client_id' => $tokens->first()->client_id,
                'name' => $tokens->first()->client->name,
                'connected_at' => $tokens->max('created_at'),
            ])
            ->values();

        return view('connections.index', compact('connections'));
    }

    public function destroy(RevokeMcpConnectionRequest $request, string $client): RedirectResponse
    {
        $tokens = $request->user()->tokens()
            ->where('client_id', $client)
            ->whereJsonContains('scopes', Registrar::OAUTH_SCOPE)
            ->where('revoked', false)
            ->get();

        abort_if($tokens->isEmpty(), 404);

        $tokenIds = $tokens->pluck('id');
        Token::query()->whereIn('id', $tokenIds)->update(['revoked' => true]);
        RefreshToken::query()->whereIn('access_token_id', $tokenIds)->update(['revoked' => true]);

        return redirect()->route('connections.index')->with('status', 'Agent connection revoked.');
    }
}
