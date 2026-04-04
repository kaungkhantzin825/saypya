<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:student,lecturer'],
            'terms' => ['required', 'accepted'],
        ]);

        // Store user data in session temporarily
        session([
            'registration_data' => [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]
        ]);

        // Generate and send verification link
        Otp::generate($request->email, 'registration');

        return redirect()->route('verify.email.sent')
            ->with('success', 'A verification link has been sent to your email. Please check your inbox.');
    }

    /**
     * Show email sent confirmation
     */
    public function showEmailSent(): View
    {
        if (!session('registration_data')) {
            return redirect()->route('register');
        }

        return view('auth.verify-email-sent', ['type' => 'registration']);
    }

    /**
     * Verify email via link
     */
    public function verifyEmail(Request $request, $token): RedirectResponse
    {
        // Verify token
        $otpRecord = Otp::verifyByToken($token, 'registration');
        
        if (!$otpRecord) {
            return redirect()->route('login')
                ->with('error', 'Invalid or expired verification link. Please register again.');
        }

        // Get registration data from session
        $registrationData = session('registration_data');
        
        // If no session data, try to find by email from token
        if (!$registrationData) {
            $registrationData = [
                'email' => $otpRecord->email,
            ];
        }

        // Check if user already exists
        $existingUser = User::where('email', $otpRecord->email)->first();
        if ($existingUser) {
            session()->forget('registration_data');
            return redirect()->route('login')
                ->with('error', 'This email is already registered. Please login.');
        }

        // Create user with pending status (requires admin approval)
        $user = User::create([
            'name' => $registrationData['name'] ?? 'User',
            'email' => $otpRecord->email,
            'password' => $registrationData['password'] ?? Hash::make(Str::random(16)),
            'role' => $registrationData['role'] ?? 'student',
            'status' => 'pending', // User needs admin approval
            'email_verified_at' => now(),
        ]);

        event(new Registered($user));

        // Clear session data
        session()->forget('registration_data');

        return redirect()->route('login')
            ->with('success', 'Email verified successfully! Your account is pending admin approval. You will be able to login once approved.');
    }

    /**
     * Resend verification link
     */
    public function resendVerification(Request $request): RedirectResponse
    {
        $registrationData = session('registration_data');
        
        if (!$registrationData) {
            return redirect()->route('register')
                ->with('error', 'Session expired. Please register again.');
        }

        // Generate and send new verification link
        Otp::generate($registrationData['email'], 'registration');

        return back()->with('success', 'New verification link has been sent to your email.');
    }
}
