<?php

namespace App\Support;

/**
 * The single brand mark used when a record has no image of its own.
 *
 * Image accessors and view placeholders used to point at third-party services
 * (`placehold.co`, `via.placeholder.com`, `ui-avatars.com`). Those are external
 * dependencies: they render as the browser's broken-image icon whenever the
 * network blocks them, and `via.placeholder.com` has stopped resolving at all.
 * A local asset under `public/` always loads.
 *
 * `AppImage.vue` uses the same asset as its client-side fallback, so the two
 * layers agree on what "no image" looks like.
 */
class BrandImage
{
    /** Path inside `public/`. */
    public const PATH = 'images/SanPya-Logo.png';

    /** Absolute URL — for `src` attributes and API payloads. */
    public static function url(): string
    {
        return asset(self::PATH);
    }

    /** Root-relative path — for contexts that must not depend on the request host. */
    public static function path(): string
    {
        return '/' . self::PATH;
    }
}
