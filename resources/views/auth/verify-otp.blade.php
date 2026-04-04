@extends('layouts.app')

@section('title', 'Verify OTP')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="flex justify-center">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-shield-alt text-white text-2xl"></i>
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Verify Your Email
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                We've sent a 6-digit OTP to your email<br>
                <strong>{{ session('registration_data')['email'] ?? session('reset_email') }}</strong>
            </p>
        </div>
        
        @if(session('success'))
            <div class="rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
        
        <form class="mt-8 space-y-6" action="{{ route('verify.otp') }}" method="POST">
            @csrf
            
            <div>
                <label for="otp" class="sr-only">OTP Code</label>
                <input 
                    id="otp" 
                    name="otp" 
                    type="text" 
                    maxlength="6"
                    pattern="[0-9]{6}"
                    required 
                    class="appearance-none relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-center text-2xl tracking-widest @error('otp') border-red-500 @enderror" 
                    placeholder="000000"
                    autofocus
                >
                @error('otp')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button 
                    type="submit" 
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-check text-blue-500 group-hover:text-blue-400"></i>
                    </span>
                    Verify OTP
                </button>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Didn't receive the code?
                </p>
                <form action="{{ route('resend.otp') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="font-medium text-blue-600 hover:text-blue-500">
                        Resend OTP
                    </button>
                </form>
            </div>

            <div class="text-center">
                <a href="{{ route('register') }}" class="text-sm text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Registration
                </a>
            </div>
        </form>

        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        <strong>Note:</strong> OTP is valid for 10 minutes. Please check your spam folder if you don't see the email.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-format OTP input
document.getElementById('otp').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>
@endsection
