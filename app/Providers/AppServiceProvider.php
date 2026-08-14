<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Passport::authorizationView('mcp.authorize');

        RateLimiter::for('wins', function (Request $request): Limit {
            return Limit::perMinute(12)
                ->by('win-submission:'.($request->user()?->getAuthIdentifier() ?? $request->ip()))
                ->response(function (Request $request, array $headers) {
                    $message = 'You are logging wins too quickly. Please wait a moment before trying again.';

                    if ($request->expectsJson()) {
                        return response()->json(['message' => $message], 429, $headers);
                    }

                    return back()->withErrors(['content' => $message]);
                });
        });

        RateLimiter::for('mcp', function (Request $request): Limit {
            return Limit::perMinute(max(1, (int) config('mcp.max_requests_per_minute')))
                ->by('mcp:'.($request->user()?->getAuthIdentifier() ?? $request->ip()))
                ->response(fn (Request $request, array $headers) => response()->json([
                    'message' => 'Too many MCP requests. Please wait a moment before trying again.',
                ], 429, $headers));
        });
    }
}
