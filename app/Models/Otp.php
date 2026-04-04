<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Otp extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'otp',
        'type',
        'expires_at',
        'is_used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Generate and send verification link
     */
    public static function generate($email, $type = 'registration')
    {
        // Generate secure token (40 characters)
        $token = Str::random(40);
        
        // Delete old tokens for this email and type
        self::where('email', $email)
            ->where('type', $type)
            ->delete();
        
        // Create new token
        $otpRecord = self::create([
            'email' => $email,
            'otp' => $token,
            'type' => $type,
            'expires_at' => now()->addHours(24), // Token valid for 24 hours
        ]);
        
        // Send verification link via email
        self::sendVerificationEmail($email, $token, $type);
        
        return $otpRecord;
    }

    /**
     * Verify token
     */
    public static function verify($email, $token, $type = 'registration')
    {
        $otpRecord = self::where('email', $email)
            ->where('otp', $token)
            ->where('type', $type)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();
        
        if ($otpRecord) {
            $otpRecord->update(['is_used' => true]);
            return true;
        }
        
        return false;
    }

    /**
     * Verify by token only (for link verification)
     */
    public static function verifyByToken($token, $type = 'registration')
    {
        $otpRecord = self::where('otp', $token)
            ->where('type', $type)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();
        
        if ($otpRecord) {
            $otpRecord->update(['is_used' => true]);
            return $otpRecord;
        }
        
        return null;
    }

    /**
     * Send verification email with link
     */
    private static function sendVerificationEmail($email, $token, $type)
    {
        if ($type === 'registration') {
            $verificationUrl = route('verify.email', ['token' => $token]);
            $subject = 'Verify Your Email Address - Sanpya Online Academy';
            
            $message = "
                <h2>Welcome to Sanpya Online Academy!</h2>
                <p>Thank you for registering. Please click the button below to verify your email address:</p>
                <p style='margin: 30px 0;'>
                    <a href='{$verificationUrl}' style='background-color: #3B82F6; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Verify Email Address
                    </a>
                </p>
                <p>Or copy and paste this link into your browser:</p>
                <p>{$verificationUrl}</p>
                <p>This link will expire in 24 hours.</p>
                <p>If you did not create an account, no further action is required.</p>
            ";
        } else {
            $verificationUrl = route('password.verify.token', ['token' => $token]);
            $subject = 'Reset Your Password - Sanpya Online Academy';
            
            $message = "
                <h2>Password Reset Request</h2>
                <p>You are receiving this email because we received a password reset request for your account.</p>
                <p style='margin: 30px 0;'>
                    <a href='{$verificationUrl}' style='background-color: #3B82F6; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Reset Password
                    </a>
                </p>
                <p>Or copy and paste this link into your browser:</p>
                <p>{$verificationUrl}</p>
                <p>This link will expire in 24 hours.</p>
                <p>If you did not request a password reset, no further action is required.</p>
            ";
        }
        
        Mail::send([], [], function ($mail) use ($email, $subject, $message) {
            $mail->to($email)
                 ->subject($subject)
                 ->html($message);
        });
    }
}
