# Wins

Wins is a calm, private progress journal for noticing what is already going well.

It is not a task manager, habit tracker, productivity dashboard, or AI assistant. Instead of starting with another list of obligations, a person records the problems they solved, the things they finished, and the moments they are proud of. Over time, those small observations make momentum and consistency visible without turning progress into pressure.

## What it does

- Lets authenticated people record short wins throughout the day.
- Shows the current week, a running daily streak, and an infinite history grouped by date.
- Stores timestamps in UTC and uses the person’s saved time zone only when presenting their days, weekly view, and streak.
- Enforces the server-wide `WINS_MAX_PER_DAY` cap while keeping each user’s `daily_wins_goal` as a gentle agent reflection target; users can also choose `weekly_check_in_days`.
- Includes public Privacy Policy and Terms of Service pages. Wins stores account and journal data to operate the service and does not sell or resell personal information or wins.

The interface is Blade, Tailwind CSS, and Alpine.js. Laravel owns the data, authentication, validation, authorization, and rate limits.

## MCP connection

Wins exposes a remote [Model Context Protocol](https://modelcontextprotocol.io/) server through Laravel MCP. An agent can connect to:

```text
https://your-wins-domain.example/mcp/wins
```

The connection is OAuth 2.1 through Laravel Passport:

1. The agent discovers Wins’ OAuth metadata and registers a public PKCE client.
2. The person signs in to Wins and explicitly approves the connection.
3. Passport issues a user-bound token with the `mcp:use` scope.
4. Every MCP call resolves the authenticated user from that token. MCP tools never accept a user ID or email.

The server currently provides:

- `get-today-status` — today’s win count, remaining server capacity, personal reflection target, check-in status, and daily streak.
- `add-win` — records a win only when the person explicitly asks; it uses the same daily cap as the web form.
- `list-recent-wins` — returns the person’s wins in date groups with a cursor for older entries.
- `set-daily-wins-goal` — changes the person’s daily reflection target from 1 to 5; it never changes the server cap.
- `set-weekly-check-in-days` — changes which ISO weekdays an agent may initiate a reflection.

MCP requests require a valid Passport token, the `mcp:use` scope, and are rate-limited per user. Users can view and revoke connected agents from **Connections** in the application header. Revocation invalidates both access and refresh tokens.

OAuth redirect URIs are allowlisted in `config/mcp.php`; do not replace that allowlist with `*` in production. Add a provider or custom URI scheme only after confirming its OAuth callback requirements.

## Local setup

Requirements: PHP 8.3+, Composer, Node.js, and npm.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan passport:keys
npm install
npm run build
```

Use your normal Laravel/Herd workflow locally. `composer run dev` starts the application’s development processes if you prefer the bundled command.

Useful commands:

```bash
php artisan test --compact
vendor/bin/pint --dirty --format=agent
npm run build
php artisan schedule:list
```

## Production configuration

Set `APP_URL` to the canonical HTTPS application URL. Also set:

```dotenv
MCP_AUTHORIZATION_SERVER=https://your-wins-domain.example
MCP_MAX_REQUESTS_PER_MINUTE=60
WINS_MAX_PER_DAY=5
```

Passport needs a persistent signing key pair. Generate it securely with `php artisan passport:keys` on the deployment environment, or provide `PASSPORT_PRIVATE_KEY` and `PASSPORT_PUBLIC_KEY` through the deployment platform’s secret manager. Never commit those keys.

Run migrations during deployment and ensure Laravel’s scheduler runs every minute. Wins schedules `passport:purge` hourly to remove expired and revoked OAuth material.

## Testing philosophy

The test suite focuses on behavior: account access, registration, win persistence, rate limiting, date grouping, streak calculation, MCP token scope enforcement, user isolation, and connection revocation. It deliberately avoids asserting incidental Blade copy or Alpine implementation details.
