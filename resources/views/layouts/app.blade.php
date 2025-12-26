<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sanpya Online Academy') }} - @yield('title', 'Online Learning Platform')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Myanmar Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Myanmar:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://mmwebfonts.comquas.com/fonts/?font=pyidaungsu" rel="stylesheet" type="text/css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { 
            font-family: 'Pyidaungsu', 'Noto Sans Myanmar', 'Inter', sans-serif; 
        }
        .myanmar-text {
            font-family: 'Pyidaungsu', 'Noto Sans Myanmar', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 antialiased">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center space-x-2">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-white text-sm"></i>
                        </div>
                        <span class="text-xl font-bold text-gray-900 myanmar-text">Sanpya Online Academy</span>
                    </a>
                </div>

                <!-- Search Bar -->
                <div class="flex-1 max-w-lg mx-8">
                    <form action="{{ route('search') }}" method="GET" class="relative">
                        <input type="text" name="q" value="{{ request('q') }}" 
                               placeholder="{{ __('app.search_placeholder') }}" 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </form>
                </div>

                <!-- Navigation Links -->
                <div class="flex items-center space-x-6">
                    <a href="{{ route('courses.index') }}" class="text-gray-700 hover:text-blue-600 font-medium">{{ __('app.courses') }}</a>
                    
                    <!-- Language Switcher -->
                    <div class="relative group">
                        <button class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 font-medium">
                            <i class="fas fa-globe"></i>
                            <span>{{ config('app.supported_locales')[app()->getLocale()] }}</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        
                        <div class="absolute right-0 mt-2 w-32 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <div class="py-2">
                                @foreach(config('app.supported_locales') as $locale => $name)
                                    <a href="{{ route('language.switch', $locale) }}" 
                                       class="block px-4 py-2 text-gray-700 hover:bg-gray-50 {{ app()->getLocale() == $locale ? 'bg-blue-50 text-blue-600' : '' }}">
                                        {{ $name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    @auth
                        @if(auth()->user()->isLecturer())
                            <a href="{{ route('instructor.dashboard') }}" class="text-gray-700 hover:text-blue-600 font-medium">{{ __('app.teach') }}</a>
                        @endif
                        
                        <div class="relative group">
                            <button class="flex items-center space-x-2 text-gray-700 hover:text-blue-600">
                                <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="w-8 h-8 rounded-full">
                                <span class="font-medium">{{ auth()->user()->name }}</span>
                                <i class="fas fa-chevron-down text-xs"></i>
                            </button>
                            
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                                <div class="py-2">
                                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">
                                        <i class="fas fa-tachometer-alt mr-2"></i>{{ __('app.dashboard') }}
                                    </a>
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">
                                        <i class="fas fa-user mr-2"></i>{{ __('app.profile') }}
                                    </a>
                                    <a href="{{ route('my.courses') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">
                                        <i class="fas fa-book mr-2"></i>{{ __('app.my_courses') }}
                                    </a>
                                    <a href="{{ route('my.wishlist') }}" class="block px-4 py-2 text-gray-700 hover:bg-gray-50">
                                        <i class="fas fa-heart mr-2"></i>{{ __('app.wishlist') }}
                                    </a>
                                    <hr class="my-2">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-gray-50">
                                            <i class="fas fa-sign-out-alt mr-2"></i>{{ __('app.logout') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600 font-medium">{{ __('app.login') }}</a>
                        <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">{{ __('app.register') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative max-w-7xl mx-auto mt-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative max-w-7xl mx-auto mt-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-white text-sm"></i>
                        </div>
                        <span class="text-xl font-bold myanmar-text">Sanpya Online Academy</span>
                    </div>
                    <p class="text-gray-400 mb-4">Empowering learners worldwide with quality online education.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Company</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Careers</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Press</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Blog</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Support</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Terms of Service</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-4">Teaching</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Become an Instructor</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Teaching Guidelines</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Instructor Support</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} LearnHub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>