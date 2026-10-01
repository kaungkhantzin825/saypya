<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root Blade template rendered on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Props shared with every Inertia page.
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [
            'app' => [
                'name' => config('app.name', 'Sanpya Online Academy'),
                'locale' => app()->getLocale(),
                'locales' => config('app.supported_locales', ['en' => 'English']),
                'registrationEnabled' => Setting::get('registration_enabled', '1') === '1',
            ],

            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'avatar_url' => $user->avatar_url,
                    'bio' => $user->bio,
                    'phone' => $user->phone,
                    'country' => $user->country,
                    'is_admin' => $user->isAdmin(),
                    'is_lecturer' => $user->isLecturer(),
                    'is_super_admin' => $user->isSuperAdmin(),
                ] : null,
                // Lazily evaluated: only queried when a page actually reads it.
                'wishlistCount' => fn () => $user ? $user->wishlist()->count() : 0,
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
        ]);
    }
}
