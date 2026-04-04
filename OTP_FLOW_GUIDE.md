# Email Verification Link System

## Overview
The system uses Gmail SMTP to send verification links for:
1. User Registration
2. Password Reset

## Configuration

### Gmail Settings (.env)
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=webdeveloperkkz@gmail.com
MAIL_PASSWORD="rbii fiuz hlpq zcnz"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=webdeveloperkkz@gmail.com
MAIL_FROM_NAME="Sanpya Online Academy"
```

## Registration Flow with Email Verification Link

1. User fills registration form → `/register` (POST)
2. System validates data and stores in session
3. Secure token generated (40 characters) and sent to email as clickable link
4. User redirected to confirmation page → `/verify-email-sent` (GET)
5. User clicks verification link in email → `/verify-email/{token}` (GET)
6. System verifies token:
   - Valid: User account created with "pending" status
   - Invalid: Error message, redirect to login
7. User redirected to login page with message: "Email verified! Your account is pending admin approval."
8. Admin reviews and approves/rejects user in admin panel
9. Once approved, user can login normally

### Admin Approval Required:
- New users are created with status = "pending"
- Users cannot login until admin approves them
- Admin sees notification badge with pending users count
- Admin can approve or reject users from Users Management page

### Files Involved:
- Controller: `app/Http/Controllers/Auth/RegisteredUserController.php`
- View: `resources/views/auth/verify-email-sent.blade.php`
- Routes: `routes/auth.php`
- Admin: `resources/views/admin/users/index.blade.php`

## Password Reset Flow with Verification Link

1. User clicks "Forgot Password" → `/forgot-password` (GET)
2. User enters email → `/forgot-password` (POST)
3. System validates email exists in database
4. Secure token generated (40 characters) and sent to email as clickable link
5. User redirected to confirmation page → `/password-link-sent` (GET)
6. User clicks reset link in email → `/reset-password/{token}` (GET)
7. System verifies token:
   - Valid: Redirect to reset password form
   - Invalid: Error message, redirect to forgot password
8. User enters new password → `/reset-password` (GET)
9. User submits new password → `/reset-password` (POST)
10. Password updated, redirect to login with success message

### Files Involved:
- Controller: `app/Http/Controllers/Auth/PasswordResetController.php`
- Views:
  - `resources/views/auth/forgot-password.blade.php`
  - `resources/views/auth/password-link-sent.blade.php`
  - `resources/views/auth/reset-password.blade.php`
- Routes: `routes/auth.php`

## Verification Token Model Features

### Location: `app/Models/Otp.php`

### Key Methods:
1. `generate($email, $type)` - Generates 40-character secure token and sends email with link
2. `verify($email, $token, $type)` - Verifies token is valid and not expired
3. `verifyByToken($token, $type)` - Verifies token without email (for link clicks)
4. `sendVerificationEmail($email, $token, $type)` - Sends verification link via Gmail SMTP

### Token Properties:
- Length: 40 characters (random string)
- Validity: 24 hours
- Types: 'registration' or 'password_reset'
- One-time use: Marked as used after verification
- Auto-cleanup: Old tokens deleted when new one generated

## Database Table: otps

```sql
- id (bigint, primary key)
- email (string)
- otp (string, 40 characters - now stores token)
- type (enum: 'registration', 'password_reset')
- expires_at (timestamp)
- is_used (boolean, default: false)
- created_at (timestamp)
- updated_at (timestamp)
```

## Testing the Flow

### Test Registration:
1. Go to `/register`
2. Fill form with valid data
3. Check email for verification link
4. Click "Verify Email Address" button in email
5. Should redirect to login with pending approval message
6. Admin approves user
7. User can now login

### Test Password Reset:
1. Go to `/login`
2. Click "Forgot Password"
3. Enter registered email
4. Check email for reset link
5. Click "Reset Password" button in email
6. Enter new password
7. Should redirect to login with success message

## User Status System

### Status Types:
1. **pending** - New user waiting for admin approval (cannot login)
2. **active** - Approved user (can login normally)
3. **inactive** - Deactivated user (cannot login)

### Login Restrictions:
- Users with "pending" status see error: "သင့်အကောင့်သည် အက်ဒမင်၏ အတည်ပြုချက်ကို စောင့်ဆိုင်းနေပါသည်။"
- Users with "inactive" status see error: "သင့်အကောင့်ကို ပိတ်ထားပါသည်။"
- Only "active" users can login successfully

### Admin Features:
- Pending users notification badge in sidebar
- Alert on dashboard showing pending count
- Filter users by status (Pending/Active/Inactive)
- Approve button - changes status to "active"
- Reject button - changes status to "inactive"
- Direct link to pending users: `/admin/users?status=pending`

## Security Features

1. Token expires after 24 hours
2. Token can only be used once
3. Old tokens automatically deleted when new one generated
4. Session-based verification (prevents URL manipulation)
5. Email validation before token generation
6. Password strength validation (minimum 8 characters)
7. Secure 40-character random tokens (not guessable)

## Troubleshooting

### Verification Email Not Received:
1. Check Gmail credentials in .env
2. Verify Gmail app password is correct
3. Check spam/junk folder
4. Ensure MAIL_ENCRYPTION=tls and MAIL_PORT=587

### Session Expired Error:
- User took too long, need to restart process
- Clear browser cache and try again

### Invalid Token Error:
- Token expired (>24 hours)
- Token already used
- Use "Resend Link" button to get new verification link

## Routes Summary

### Registration Routes:
- GET `/register` - Show registration form
- POST `/register` - Process registration, send verification link
- GET `/verify-email-sent` - Show email sent confirmation
- GET `/verify-email/{token}` - Verify email via link
- POST `/resend-verification` - Resend verification link

### Password Reset Routes:
- GET `/forgot-password` - Show forgot password form
- POST `/forgot-password` - Send reset link to email
- GET `/password-link-sent` - Show link sent confirmation
- GET `/reset-password/{token}` - Verify token and show reset form
- GET `/reset-password` - Show new password form
- POST `/reset-password` - Update password

## UI Features

### Myanmar Language Support:
- All forms use Myanmar text with "myanmar-text" CSS class
- Myanmar fonts: Pyidaungsu, Noto Sans Myanmar
- Clean, modern UI with gradient icons
- Responsive design for mobile devices

### User Experience:
- Auto-submit OTP when 6 digits entered
- Password visibility toggle
- Clear error/success messages
- Resend OTP button
- Back to login links
- Loading states and validation
