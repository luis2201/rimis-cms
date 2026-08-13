<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureResearcherEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->isMember() && ! $request->user()->hasVerifiedEmail()) {
            return redirect()
                ->route('verification.notice')
                ->with('warning', 'Confirma tu correo electrónico para acceder al área privada.');
        }

        return $next($request);
    }
}
