<?php

namespace App\Providers;

use App\Models\Menu;
use App\Support\MailSettingsManager;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        app(MailSettingsManager::class)->apply();

        Paginator::useBootstrap();

        View::composer([
            'welcome',
            'pages.show',
            'news.index',
            'news.listing',
            'news.show',
            'bulletins.index',
            'bulletins.show',
            'events.index',
            'events.show',
            'calls.index',
            'calls.show',
            'research-publications.index',
            'research-publications.show',
            'researchers.index',
            'researchers.show',
            'institutions.index',
            'institutions.show',
        ], function ($view) {
            $publicMenus = Schema::hasTable('menus')
                ? Menu::where('is_active', true)->with('rootItems')->get()->keyBy('location')
                : collect();

            $view->with('publicMenus', $publicMenus);
        });
    }
}
