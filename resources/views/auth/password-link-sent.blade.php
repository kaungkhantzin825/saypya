@extends('layouts.app')

@section('title', 'စကားဝှက်ပြန်လည်သတ်မှတ်ရန်')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="flex justify-center">
                <div class="w-20 h-20 bg-gradient-to-r from-purple-500 to-blue-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-envelope-open text-white text-3xl"></i>
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 myanmar-text">
                အီးမေးလ်စစ်ဆေးပါ
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 myanmar-text">
                စကားဝှက်ပြန်လည်သတ်မှတ်ရန် လင့်ခ်ကို သင့်အီးမေးလ်သို့ ပို့ပြီးပါပြီ
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

        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 myanmar-text">
                        နောက်ထပ်လုပ်ဆောင်ရမည့်အဆင့်များ:
                    </h3>
                    <div class="mt-2 text-sm text-blue-700 myanmar-text">
                        <ol class="list-decimal list-inside space-y-1">
                            <li>သင့်အီးမေးလ် inbox ကိုစစ်ဆေးပါ</li>
                            <li>Sanpya Online Academy မှ အီးမေးလ်ကိုရှာပါ</li>
                            <li>"Reset Password" ခလုတ်ကိုနှိပ်ပါ</li>
                            <li>စကားဝှက်အသစ်ကို ရိုက်ထည့်ပါ</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700 myanmar-text">
                        လင့်ခ်သည် ၂၄ နာရီအတွင်း သက်တမ်းကုန်ဆုံးပါမည်။ အီးမေးလ်မရရှိပါက spam folder ကိုစစ်ဆေးပါ။
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center space-y-3">
            <p class="text-sm text-gray-600 myanmar-text">
                အီးမေးလ်မရရှိဘူးလား?
            </p>
            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <input type="hidden" name="email" value="{{ session('password_reset_email') }}">
                <button type="submit" class="font-medium text-blue-600 hover:text-blue-500 myanmar-text">
                    <i class="fas fa-redo mr-1"></i>
                    လင့်ခ် ပြန်ပို့ပါ
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
@endsection
