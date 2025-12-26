<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    public function switch(Request $request, $locale)
    {
        // Validate locale
        $supportedLocales = array_keys(config('app.supported_locales', ['en' => 'English']));
        if (!in_array($locale, $supportedLocales)) {
            $locale = 'en';
        }
        
        // Store locale in session
        Session::put('locale', $locale);
        
        return redirect()->back();
    }
}