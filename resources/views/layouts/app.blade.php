<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sanpya Online Academy') }} - @yield('title', 'Online Learning Platform')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Myanmar:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Noto Sans Myanmar', 'Inter', sans-serif; }
        .myanmar-text { font-family: 'Noto Sans Myanmar', sans-serif; }
        
        /* 3D Glossy Button Style */
        .btn-3d {
            display: inline-block;
            padding: 10px 24px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-shadow: 0 1px 1px rgba(0,0,0,0.2);
        }
        
        .btn-3d-cyan {
            background: linear-gradient(180deg, #5dd3e8 0%, #2bb8cc 50%, #1a9aad 100%);
            box-shadow: 0 4px 0 #158a9c, 0 6px 10px rgba(0,0,0,0.3);
            color: white;
        }
        .btn-3d-cyan:hover {
            transform: translateY(2px);
            box-shadow: 0 2px 0 #158a9c, 0 4px 6px rgba(0,0,0,0.3);
        }
        
        .btn-3d-teal {
            background: linear-gradient(180deg, #4fd1c5 0%, #319795 50%, #2c7a7b 100%);
            box-shadow: 0 4px 0 #285e61, 0 6px 10px rgba(0,0,0,0.3);
            color: white;
        }
        .btn-3d-teal:hover {
            transform: translateY(2px);
            box-shadow: 0 2px 0 #285e61, 0 4px 6px rgba(0,0,0,0.3);
        }
        
        .btn-3d-red {
            background: linear-gradient(180deg, #fc8181 0%, #e53e3e 50%, #c53030 100%);
            box-shadow: 0 4px 0 #9b2c2c, 0 6px 10px rgba(0,0,0,0.3);
            color: white;
        }
        .btn-3d-red:hover {
            transform: translateY(2px);
            box-shadow: 0 2px 0 #9b2c2c, 0 4px 6px rgba(0,0,0,0.3);
        }
        
        .btn-3d-white {
            background: linear-gradient(180deg, #ffffff 0%, #f7fafc 50%, #edf2f7 100%);
            box-shadow: 0 4px 0 #cbd5e0, 0 6px 10px rgba(0,0,0,0.2);
            color: #2d3748;
        }
        .btn-3d-white:hover {
            transform: translateY(2px);
            box-shadow: 0 2px 0 #cbd5e0, 0 4px 6px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body class="bg-gray-50 antialiased">
    <!-- Navigation -->
    <nav class="bg-white shadow-md border-b-4 border-purple-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center flex-shrink-0">
                    <img src="{{ asset('images/SanPya-Logo.png') }}" alt="Sanpya Academy" style="height: 50px; width: auto;">
                </a>

                <!-- Center Menu -->
                <div class="flex items-center" style="gap: 3rem;">
                    <!-- Services Dropdown -->
                    <div class="relative group">
                        <button class="text-gray-800 hover:text-teal-600 font-semibold flex items-center text-base py-2">
                            Services <i class="fas fa-chevron-down ml-2 text-xs"></i>
                        </button>
                        <div class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-xl border opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                            <a href="{{ route('home') }}" class="block px-4 py-3 text-gray-700 hover:bg-teal-50 hover:text-teal-600 rounded-t-lg">Home</a>
                            <a href="{{ route('courses.index') }}" class="block px-4 py-3 text-gray-700 hover:bg-teal-50 hover:text-teal-600">All Courses</a>
                            <a href="{{ route('categories.index') }}" class="block px-4 py-3 text-gray-700 hover:bg-teal-50 hover:text-teal-600">Categories</a>
                            <!-- <a href="#" class="block px-4 py-3 text-gray-700 hover:bg-teal-50 hover:text-teal-600 rounded-b-lg">Live Classes</a> -->
                        </div>
                    </div>
                    
                    <!-- About Us Dropdown -->
                    <div class="relative group">
                        <button class="text-gray-800 hover:text-teal-600 font-semibold flex items-center text-base py-2">
                            About Us <i class="fas fa-chevron-down ml-2 text-xs"></i>
                        </button>
                        <div class="absolute left-0 mt-2 w-48 bg-white rounded-lg shadow-xl border opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                            <a href="{{ route('about') }}" class="block px-4 py-3 text-gray-700 hover:bg-teal-50 hover:text-teal-600 rounded-t-lg">Our Story</a>
                            <!-- <a href="{{ route('team') }}" class="block px-4 py-3 text-gray-700 hover:bg-teal-50 hover:text-teal-600">Our Team</a> -->
                            <a href="{{ route('partners') }}" class="block px-4 py-3 text-gray-700 hover:bg-teal-50 hover:text-teal-600 rounded-b-lg">Partners</a>
                        </div>
                    </div>
                    
                    <!-- Contact Us -->
                    <a href="{{ route('contact') }}" class="text-gray-800 hover:text-teal-600 font-semibold text-base py-2">Contact Us</a>
                </div>

                <!-- Right Side - Buttons -->
                <div class="flex items-center space-x-3 flex-shrink-0">
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="btn-3d btn-3d-teal">
                                Admin Panel
                            </a>
                        @elseif(auth()->user()->isLecturer())
                            <a href="{{ route('instructor.dashboard') }}" class="btn-3d btn-3d-teal">
                                Instructor Panel
                            </a>
                        @else
                            <a href="{{ route('dashboard') }}" class="btn-3d btn-3d-cyan">
                                My Dashboard
                            </a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="btn-3d btn-3d-red">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn-3d btn-3d-teal">
                            Instructor Login
                        </a>
                        <a href="{{ route('login') }}" class="btn-3d btn-3d-cyan">
                            Student Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 max-w-7xl mx-auto mt-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 max-w-7xl mx-auto mt-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-800 text-white" style="background-color: #2d2d30;">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-3 mb-4">
                        <img src="{{ asset('images/SanPya-Logo.png') }}" alt="Sanpya Academy" style="height: 50px; width: auto;">
                        <span class="text-xl font-bold">Sanpya Academy</span>
                    </div>
                    <p class="text-gray-400 mb-4">Empowering learners with quality online education.</p>
                    <div class="flex space-x-3">
                        <a href="#" class="w-9 h-9 bg-slate-700 rounded-full flex items-center justify-center hover:bg-teal-500"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="w-9 h-9 bg-slate-700 rounded-full flex items-center justify-center hover:bg-teal-500"><i class="fab fa-youtube"></i></a>

                    </div>
                </div>
                <div>
                    <h3 class="font-semibold mb-4 text-teal-400">Company</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('about') }}" class="hover:text-white">About Us</a></li>
                        <li><a href="{{ route('partners') }}" class="hover:text-white">Partners</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold mb-4 text-teal-400">Support</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('contact') }}" class="hover:text-white">Contact Us</a></li>
                        <li><a href="#" class="hover:text-white">Help Center</a></li>
                        <li><a href="#" class="hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white">Terms of Service</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold mb-4 text-teal-400">Teaching</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Become an Instructor</a></li>
                        <li><a href="#" class="hover:text-white">Teaching Guidelines</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="border-t border-slate-700 py-4">
            <div class="max-w-7xl mx-auto px-4 flex justify-between items-center text-gray-400 text-sm">
                <p>&copy; {{ date('Y') }} Sanpya Online Academy</p>
                <p>Developed By Grace Myanmar Software Solutions</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
