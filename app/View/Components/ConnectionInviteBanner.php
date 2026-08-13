<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class ConnectionInviteBanner extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.connection-invite-banner');
    }

    public function shouldRender(): bool
    {
        return ! $this->userHasConnections();
    }

    protected function userHasConnections(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return $user
            ->connections()
            ->exists();
    }
}
