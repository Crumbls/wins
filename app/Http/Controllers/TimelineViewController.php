<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTimelineViewRequest;
use Illuminate\Http\Response;

class TimelineViewController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateTimelineViewRequest $request): Response
    {
        $request->user()->update(['wins_timeline_view' => $request->validated('view')]);

        return response()->noContent();
    }
}
