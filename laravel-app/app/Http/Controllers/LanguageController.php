<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Redirect;
use App\Language;

class LanguageController extends Controller
{
    public function switchLanguage($locale)
    {
        $locale = in_array($locale, ['en', 'fr'], true) ? $locale : 'en';
        setcookie('language', $locale, time() + (86400 * 365), "/");
        \App::setLocale($locale);

        $back = url()->previous();
        if (! $back || $back === url()->current()) {
            return redirect('/');
        }

        return Redirect::back();
    }
}
