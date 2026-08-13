<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools\AddWin;
use App\Mcp\Tools\GetTodayStatus;
use App\Mcp\Tools\ListRecentWins;
use App\Mcp\Tools\SetDailyWinsGoal;
use App\Mcp\Tools\SetWeeklyCheckInDays;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

#[Name('Wins Server')]
#[Version('1.0.0')]
#[Instructions('Wins is a private progress journal. Only read or add wins for the signed-in user. Wins are reflections on what went well, not tasks or goals. Use add-win only when the user explicitly asks to record a win. Before initiating a reflection, use get-today-status and only ask when is_check_in_day is true and both remaining_goal_wins and remaining_wins are greater than zero. The personal reflection goal never overrides the server-enforced daily limit. On a non-check-in day, never initiate a prompt, but still record a win when the user explicitly asks.')]
class WinsServer extends Server
{
    protected array $tools = [
        GetTodayStatus::class,
        AddWin::class,
        ListRecentWins::class,
        SetDailyWinsGoal::class,
        SetWeeklyCheckInDays::class,
    ];
}
