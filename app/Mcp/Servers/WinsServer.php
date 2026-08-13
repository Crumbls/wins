<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\AddWin;
use App\Mcp\Tools\GetTodayStatus;
use App\Mcp\Tools\ListRecentWins;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Wins Server')]
#[Version('1.0.0')]
#[Instructions('Wins is a private progress journal. Only read or add wins for the signed-in user. Wins are reflections on what went well, not tasks or goals. Use add-win only when the user explicitly asks to record a win.')]
class WinsServer extends Server
{
    protected array $tools = [
        GetTodayStatus::class,
        AddWin::class,
        ListRecentWins::class,
    ];
}
