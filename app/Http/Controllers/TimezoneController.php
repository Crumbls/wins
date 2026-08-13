<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTimezoneRequest;
use Illuminate\Http\Response;

class TimezoneController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateTimezoneRequest $request): Response
    {
        $request->user()->update($request->validated());

        return response()->noContent();
    }
}
