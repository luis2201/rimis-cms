<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class EnsureResearcherRole
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless($request->user()?->hasRole('INVESTIGADOR'), 403);
        return $next($request);
    }
}
