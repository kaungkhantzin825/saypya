@extends('layouts.app')

@section('title', __('app.login'))

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="flex justify-center">
                <img src="{{ asset('images/SanPya-Logo.png') }}" alt="Sanpya Academy" style="width: 80px; height: auto;">
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">
                {{ __('app.welcome_back') }}
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">
                {{ __('app.dont_have_account') }}
                <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-500">
                    {{ __('app.register') }}
                </a>
            </p>
        </div>
        
        <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
            @csrf
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">{{ __('app.email') }}</label>
                    <input id="email" name="email" type="email" autocomplete="email" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}" 
                           placeholder="{{ __('app.email') }}" value="{{ old('email') }}">
                </div>
                <div>
                    <label for="password" class="sr-only">{{ __('app.password') }}</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}" 
                           placeholder="{{ __('app.password') }}">
                </div>
            </div>

            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                There were errors with your submission
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember" type="checkbox" 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">
                        {{ __('app.remember_me') }}
                    </label>
                </div>

                <div class="text-sm">
                    <a href="{{ route('password.request') }}" class="font-medium text-blue-600 hover:text-blue-500 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">
                        {{ __('app.forgot_password') }}
                    </a>
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-lock text-blue-500 group-hover:text-blue-400"></i>
                    </span>
                    {{ __('app.login') }}
                </button>
            </div>

            <!-- <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300" />
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-50 text-gray-500">Demo Accounts</span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-3">
                    <div class="text-center">
                        <p class="text-xs text-gray-600 mb-2">Quick login for testing:</p>
                        <div class="space-y-1 text-xs text-gray-500">
                            <div>Admin: admin@learnhub.com / password</div>
                            <div>Instructor: sarah@learnhub.com / password</div>
                            <div>Student: john.doe@example.com / password</div>
                        </div>
                    </div>
                </div>
            </div> -->
        </form>
    </div>
</div>
@endsection