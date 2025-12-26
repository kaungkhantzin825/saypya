@extends('layouts.app')

@section('title', 'စကားဝှက်မေ့နေပါသလား')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="flex justify-center">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-key text-white text-2xl"></i>
                </div>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 myanmar-text">
                စကားဝှက်မေ့နေပါသလား?
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 myanmar-text">
                စိတ်မပူပါနှင့်။ သင့်အီးမေးလ်လိပ်စာကို ရိုက်ထည့်ပါ။ စကားဝှက်ပြန်လည်သတ်မှတ်ရန် လင့်ခ်ပို့ပေးပါမည်။
            </p>
        </div>
        
        <div class="mt-8 space-y-6">
            <div class="rounded-md shadow-sm">
                <div>
                    <label for="email" class="sr-only">အီးမေးလ်လိပ်စာ</label>
                    <input id="email" name="email" type="email" autocomplete="email" required 
                           class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" 
                           placeholder="အီးမေးလ်လိပ်စာ">
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 myanmar-text">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-paper-plane text-blue-500 group-hover:text-blue-400"></i>
                    </span>
                    စကားဝှက်ပြန်လည်သတ်မှတ်ရန် လင့်ခ်ပို့ပါ
                </button>
            </div>

            <div class="text-center">
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500 myanmar-text">
                    ဝင်ရောက်ရန်စာမျက်နှာသို့ ပြန်သွားရန်
                </a>
            </div>
        </div>
    </div>
</div>
@endsection