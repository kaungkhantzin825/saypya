@extends('layouts.app')

@section('title', 'OTP အတည်ပြုခြင်း')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="flex justify-center">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-shield-alt text-white text-2xl"></i>
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 myanmar-text">
                OTP အတည်ပြုခြင်း
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 myanmar-text">
                သင့်အီးမေးလ်သို့ ပို့ထားသော 6 လုံးပါ OTP ကုဒ်ကို ရိုက်ထည့်ပါ
            </p>
            <p class="mt-1 text-center text-sm font-medium text-blue-600">
                {{ session('password_reset_email') }}
            </p>
        </div>

        @if (session('success'))
            <div class="rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800 myanmar-text">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800 myanmar-text">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('password.otp.verify') }}" class="mt-8 space-y-6">
            @csrf
            <div>
                <label for="otp" class="block text-sm font-medium text-gray-700 myanmar-text mb-2">
                    OTP ကုဒ်
                </label>
                <input id="otp" 
                       name="otp" 
                       type="text" 
                       maxlength="6"
                       pattern="[0-9]{6}"
                       required 
                       autofocus
                       class="appearance-none rounded-md relative block w-full px-3 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 text-center text-2xl tracking-widest focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('otp') border-red-500 @enderror" 
                       placeholder="000000">
                @error('otp')
                    <p class="mt-2 text-sm text-red-600 myanmar-text">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 myanmar-text">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-check text-blue-500 group-hover:text-blue-400"></i>
                    </span>
                    အတည်ပြုပါ
                </button>
            </div>
        </form>

        <div class="text-center space-y-2">
            <p class="text-sm text-gray-600 myanmar-text">
                OTP မရရှိဘူးလား?
            </p>
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <input type="hidden" name="email" value="{{ session('password_reset_email') }}">
                <button type="submit" class="font-medium text-blue-600 hover:text-blue-500 myanmar-text">
                    OTP ပြန်ပို့ပါ
                </button>
            </form>
        </div>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-500 myanmar-text">
                <i class="fas fa-arrow-left mr-1"></i>
                ဝင်ရောက်ရန်စာမျက်နှာသို့ ပြန်သွားရန်
            </a>
        </div>
    </div>
</div>

<script>
    // Auto-submit when 6 digits are entered
    document.getElementById('otp').addEventListener('input', function(e) {
        if (e.target.value.length === 6) {
            e.target.form.submit();
        }
    });
</script>
@endsection
