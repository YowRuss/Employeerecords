<?php

namespace App\Http\Middleware;

use App\Enums\PositionCategory;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsTeachingEmployee
{
    /**
     * Ensure the authenticated user holds a Teaching position.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user?->position || $user->position->category !== PositionCategory::Teaching) {
            abort(403, 'Unauthorized Access: Only teaching personnel can claim or manage seminar credits.');
        }

        return $next($request);
    }
}
