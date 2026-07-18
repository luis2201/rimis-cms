<?php

namespace App\Providers;

use App\Models\MenuItem;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        Route::model('menuItem', MenuItem::class);

        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('authentication',fn(Request $request)=>Limit::perMinute(10)->by(mb_strtolower((string)$request->input('email')).'|'.$request->ip()));
        RateLimiter::for('registration',fn(Request $request)=>Limit::perHour(5)->by($request->ip()));
        RateLimiter::for('password-reset',fn(Request $request)=>Limit::perHour(5)->by(mb_strtolower((string)$request->input('email')).'|'.$request->ip()));
        RateLimiter::for('public-search',fn(Request $request)=>Limit::perMinute(60)->by($request->ip()));
        RateLimiter::for('submission',fn(Request $request)=>Limit::perMinute(10)->by($request->user()?->id?:$request->ip()));
        RateLimiter::for('download',fn(Request $request)=>Limit::perMinute(30)->by($request->user()?->id?:$request->ip()));
        RateLimiter::for('health',fn(Request $request)=>Limit::perMinute(30)->by($request->ip()));
    }
}
