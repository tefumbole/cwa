<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        $locale = $request->cookie('language');
        if (! $locale && ! empty($_COOKIE['language'])) {
            $locale = $_COOKIE['language'];
        }
        if (! is_string($locale) || ! is_dir(resource_path('lang/'.$locale))) {
            $locale = 'en';
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
