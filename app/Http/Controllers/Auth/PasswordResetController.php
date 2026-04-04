<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Send verification link for password reset
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.required' => 'အီးမေးလ်လိပ်စာ ထည့်ရန်လိုအပ်ပါသည်။',
            'email.email' => 'အီးမေးလ်လိပ်စာ မှန်ကန်မှုမရှိပါ။',
            'email.exists' => 'ဤအီးမေးလ်လိပ်စာဖြင့် မှတ်ပုံတင်ထားသော အကောင့်မရှိပါ။',
        ]);

        // Store email in session
        session(['password_reset_email' => $request->email]);

        // Generate and send verification link
        Otp::generate($request->email, 'password_reset');

        return redirect()->route('password.link.sent')
            ->with('success', 'A password reset link has been sent to your email.');
    }

    /**
     * Show link sent confirmation
     */
    public function showLinkSent(): View
    {
        if (!session('password_reset_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.password-link-sent');
    }

    /**
     * Verify token and show reset form
     */
    public function verifyToken($token): mixed
    {
        // Verify token
        $otpRecord = Otp::where('otp', $token)
            ->where('type', 'password_reset')
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();
        
        if (!$otpRecord) {
            return redirect()->route('password.request')
                ->with('error', 'Invalid or expired password reset link.');
        }

        // Store email and token in session
        session([
            'password_reset_email' => $otpRecord->email,
            'password_reset_token' => $token,
        ]);

        return redirect()->route('password.reset');
    }

    /**
     * Show reset password form
     */
    public function showResetForm(): View
    {
        if (!session('password_reset_token')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    /**
     * Reset password
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = session('password_reset_email');
        $token = session('password_reset_token');
        
        if (!$email || !$token) {
            return redirect()->route('password.request')
                ->with('error', 'Session expired. Please try again.');
        }

        // Verify token one more time and mark as used
        $otpRecord = Otp::verifyByToken($token, 'password_reset');
        
        if (!$otpRecord) {
            return redirect()->route('password.request')
                ->with('error', 'Invalid or expired reset link. Please try again.');
        }

        // Update password
        $user = User::where('email', $email)->first();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Clear session data
        session()->forget(['password_reset_email', 'password_reset_token']);

        return redirect()->route('login')
            ->with('success', 'Password reset successful! Please login with your new password.');
    }
}
