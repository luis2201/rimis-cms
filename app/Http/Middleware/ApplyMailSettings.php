<?php

namespace App\Http\Middleware;

use App\Support\MailSettingsManager;
use Closure;
use Illuminate\Http\Request;

class ApplyMailSettings
{
    public function handle(Request $request, Closure $next)
    {
        app(MailSettingsManager::class)->apply();

        return $next($request);
    }
}
