<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use DB;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->assertIsolatedDatabase();

        /*if( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
            URL::forceScheme('https');
        }*/
        //setting language
        if (isset($_COOKIE['language']) && is_dir(resource_path('lang/'.$_COOKIE['language']))) {
            \App::setLocale($_COOKIE['language']);
            \Carbon\Carbon::setLocale($_COOKIE['language']);
        } else {
            \App::setLocale('en');
        }
        Schema::defaultStringLength(191);

        // Guard against boot before the database is installed/migrated (fresh install, CLI, migrations).
        if (! $this->settingsAvailable()) {
            View::share('general_setting', null);
            View::share('currency', '');
            View::share('alert_product', 0);
            return;
        }

        //get general setting value
        $general_setting = DB::table('general_settings')->latest()->first();
        // Live version from laravel-app/VERSION (updated on each commit/push)
        if ($general_setting) {
            $general_setting->app_version = \App\Support\AppVersion::erp();
        }
        $currency = $general_setting ? (\App\Currency::find($general_setting->currency) ?? '') : '';
        View::share('general_setting', $general_setting);
        View::share('currency', $currency);
        if ($general_setting) {
            config([
                'staff_access' => $general_setting->staff_access,
                'date_format' => $general_setting->date_format,
                'currency' => $currency ? $currency->code : null,
                'currency_position' => $general_setting->currency_position,
            ]);
        }

        $alert_product = DB::table('products')->where('is_active', true)->whereColumn('alert_quantity', '>', 'qty')->count();
        View::share('alert_product', $alert_product);

        View::composer('frontend.layout.main', function ($view) {
            $categories = Cache::remember('frontend_nav_categories', 3600, function () {
                return \App\Category::where('is_active', true)->orderBy('name')->get(['id', 'name']);
            });
            $view->with('categories', $categories);
        });
    }

    /**
     * Whether the core settings table exists and can be queried.
     * Prevents boot-time crashes on a fresh (unmigrated) database.
     */
    private function settingsAvailable()
    {
        try {
            return Schema::hasTable('general_settings');
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * CWACAM must never share BeyondTechWorld MySQL databases.
     */
    private function assertIsolatedDatabase()
    {
        $names = [
            env('DB_DATABASE'),
            env('BEYOND_DATA_DB_DATABASE'),
        ];
        foreach ($names as $name) {
            if (is_string($name) && preg_match('/beyondworld|beyondtech|u152889834_beyond/i', $name)) {
                throw new \RuntimeException(
                    'CWACAM must not use a BeyondTechWorld database ('.$name.'). '.
                    'Set DB_DATABASE and BEYOND_DATA_DB_DATABASE to the dedicated cwacam database.'
                );
            }
        }
        $hosts = [
            env('DB_HOST'),
            env('BEYOND_DATA_DB_HOST'),
        ];
        foreach ($hosts as $host) {
            if (is_string($host) && preg_match('/beyondtechworld|beyondworld|193\.203\.168\.163/i', $host)) {
                throw new \RuntimeException(
                    'CWACAM must not use a BeyondTechWorld database host ('.$host.').'
                );
            }
        }
    }
}
