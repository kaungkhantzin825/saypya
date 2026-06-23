<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    // Keywords that are common in spam messages
    private array $spamKeywords = [
        'jackpot', 'lottery', 'winner', 'casino', 'crypto', 'bitcoin',
        'investment', 'million dollar', 'click here', 'buy now', 'free money',
        'make money fast', 'work from home', 'earn extra', 'unclaimed funds',
        'wire transfer', 'bank account', 'prince', 'inheritance',
    ];

    public function submit(Request $request)
    {
        // Honeypot: bots fill this hidden field, humans don't see it
        if ($request->filled('website')) {
            return redirect()->back()->with('success', 'Thank you for contacting us! We will get back to you soon.');
        }

        $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:5000',
        ]);

        // Simple math captcha check
        $captchaAnswer  = (int) $request->input('captcha_answer');
        $captchaExpected = (int) $request->input('captcha_expected');
        if ($captchaAnswer !== $captchaExpected || $captchaExpected === 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['captcha_answer' => 'Incorrect answer. Please solve the math question.']);
        }

        // Spam keyword check in subject + message
        $combined = strtolower($request->subject . ' ' . $request->message);
        foreach ($this->spamKeywords as $keyword) {
            if (str_contains($combined, strtolower($keyword))) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['message' => 'Your message was flagged as spam. Please rewrite it.']);
            }
        }

        ContactMessage::create([
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
            'status'  => 'new',
        ]);

        return redirect()->back()->with('success', 'Thank you for contacting us! We will get back to you soon.');
    }
}
