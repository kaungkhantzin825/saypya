<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
            'canResetPassword' => true,
            'title' => 'Welcome back',
            'subtitle' => 'Sign in to continue your learning.',
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): \Symfony\Component\HttpFoundation\Response
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Check if user is approved
        $user = Auth::user();
        
        if ($user->status === 'pending') {
            Auth::logout();
            return back()->withErrors([
                'email' => 'သင့်အကောင့်သည် အက်ဒမင်၏ အတည်ပြုချက်ကို စောင့်ဆိုင်းနေပါသည်။ အတည်ပြုပြီးမှ ဝင်ရောက်နိုင်ပါမည်။',
            ])->withInput($request->only('email'));
        }
        
        if ($user->status === 'inactive') {
            Auth::logout();
            return back()->withErrors([
                'email' => 'သင့်အကောင့်ကို ပိတ်ထားပါသည်။ ကျေးဇူးပြု၍ အကူအညီဌာနသို့ ဆက်သွယ်ပါ။',
            ])->withInput($request->only('email'));
        }

        // Update last login time
        $user->update(['last_login_at' => now()]);

        // Students land on the Inertia dashboard. Lecturers and admins have their own
        // panels, which are still Blade — Inertia cannot render a Blade response, so
        // send them there with a full page load. Redirecting via /dashboard instead
        // left them stranded on the login screen, because that route bounces them on
        // a second redirect the Inertia client does not follow.
        if ($user->role !== 'student') {
            return Inertia::location(
                $user->role === 'admin'
                    ? route('admin.dashboard')
                    : route('instructor.dashboard')
            );
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}