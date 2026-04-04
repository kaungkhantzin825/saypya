@extends('layouts.app')

@section('title', 'စကားဝှက်အသစ်သတ်မှတ်ရန်')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="flex justify-center">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-lock text-white text-2xl"></i>
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 myanmar-text">
                စကားဝှက်အသစ်သတ်မှတ်ရန်
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 myanmar-text">
                သင့်အကောင့်အတွက် စကားဝှက်အသစ်ကို ရိုက်ထည့်ပါ
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

        <form method="POST" action="{{ route('password.update') }}" class="mt-8 space-y-6">
            @csrf

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 myanmar-text mb-2">
                    စကားဝှက်အသစ်
                </label>
                <div class="relative">
                    <input id="password" 
                           name="password" 
                           type="password" 
                           required 
                           autocomplete="new-password"
                           class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror" 
                           placeholder="စကားဝှက်အသစ်">
                    <button type="button" 
                            onclick="togglePassword('password')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="password-icon"></i>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-sm text-red-600 myanmar-text">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 myanmar-text mb-2">
                    စကားဝှက်အတည်ပြုရန်
                </label>
                <div class="relative">
                    <input id="password_confirmation" 
                           name="password_confirmation" 
                           type="password" 
                           required 
                           autocomplete="new-password"
                           class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500" 
                           placeholder="စကားဝှက်အတည်ပြုရန်">
                    <button type="button" 
                            onclick="togglePassword('password_confirmation')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="password_confirmation-icon"></i>
                    </button>
                </div>
            </div>

            <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700 myanmar-text">
                            စကားဝှက်သည် အနည်းဆုံး ၈ လုံးရှိရမည်
                        </p>
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 myanmar-text">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-save text-blue-500 group-hover:text-blue-400"></i>
                    </span>
                    စကားဝှက်သိမ်းဆည်းပါ
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(fieldId + '-icon');
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
