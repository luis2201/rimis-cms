<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureResearcherProfileIsComplete
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->hasRole('INVESTIGADOR') && ! $request->user()->hasCompleteResearcherProfile()) {
            return redirect()
                ->route('profile.edit')
                ->with('warning', 'Completa tu información profesional y adjunta tu currículum para continuar.');
        }

        return $next($request);
    }
}
