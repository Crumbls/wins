<?php

namespace App\View\Components;

use App\Models\User;
use App\Models\Win;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class StatusTicker extends Component
{
    public Carbon $date;

    public int $streakDays;

    public function __construct(
        ?Carbon $date = null,
        ?int $streakDays = null,
    ) {
        $user = Auth::user();

        $this->date = $date ?? now('UTC')->setTimezone($user instanceof User ? $user->timezone : 'UTC');
        $this->streakDays = $streakDays ?? ($user instanceof User ? Win::currentDailyStreakFor($user) : 0);
    }

    public function render(): View
    {
        return view('components.status-ticker');
    }
}
