<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cookie;

class LanguageController extends Controller
{
    public function switchLanguage($locale)
    {
        $locale = is_string($locale) ? $locale : 'en';
        if (! is_dir(resource_path('lang/'.$locale))) {
            $locale = 'en';
        }

        $minutes = 60 * 24 * 365;
        Cookie::queue('language', $locale, $minutes, '/');
        setcookie('language', $locale, time() + ($minutes * 60), '/');

        $back = url()->previous();
        if (! $back || $back === url()->current() || strpos($back, '/lang/') !== false) {
            $back = url('/');
        }

        return redirect($back);
    }
}
