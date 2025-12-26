<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;

class LanguageHelper
{
    /**
     * Get current locale
     */
    public static function getCurrentLocale()
    {
        return App::getLocale();
    }

    /**
     * Check if current locale is Myanmar
     */
    public static function isMyanmar()
    {
        return App::getLocale() === 'my';
    }

    /**
     * Check if current locale is English
     */
    public static function isEnglish()
    {
        return App::getLocale() === 'en';
    }

    /**
     * Get CSS class for Myanmar text
     */
    public static function getMyanmarClass()
    {
        return self::isMyanmar() ? 'myanmar-text' : '';
    }

    /**
     * Get all supported locales
     */
    public static function getSupportedLocales()
    {
        return config('app.supported_locales', ['en' => 'English']);
    }

    /**
     * Get locale display name
     */
    public static function getLocaleDisplayName($locale = null)
    {
        $locale = $locale ?? self::getCurrentLocale();
        $locales = self::getSupportedLocales();
        
        return $locales[$locale] ?? $locale;
    }
}