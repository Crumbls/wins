<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureMcpToken;
use App\Mcp\Servers\WinsServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::oauthRoutes();

Mcp::web('/mcp/wins', WinsServer::class)->middleware(['auth:api', EnsureMcpToken::class, 'throttle:mcp']);
