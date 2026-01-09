@extends('layouts.app')

@section('title', 'Home')

@section('content')
<!-- Hero Section with Background Image -->
<section class="relative bg-cover bg-center bg-no-repeat min-h-[450px] flex items-center" style="background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=1920&q=80');">
    <div class="max-w-7xl mx-auto px-4 py-16 text-center text-white">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Learn Anytime, Anywhere</h1>
        <p class="text-xl text-gray-200 mb-8 max-w-2xl mx-auto">Join thousands of learners and start building your skills today with our expert-led courses.</p>
        
        <div class="flex justify-center gap-8 mb-8">
            <div class="text-center">
                <div class="text-4xl font-bold text-yellow-400">{{ number_format($stats['total_students']) }}+</div>
                <div class="text-gray-300 text-sm">Students</div>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-yellow-400">{{ number_format($stats['total_courses']) }}+</div>
                <div class="text-gray-300 text-sm">Courses</div>
            </div>
            <div class="text-center">
                <div class="text-4xl font-bold text-yellow-400">{{ number_format($stats['total_instructors']) }}+</div>
                <div class="text-gray-300 text-sm">Instructors</div>
            </div>
        </div>
        
        <a href="{{ route('courses.index') }}" class="btn-3d btn-3d-cyan text-lg">
            Browse Courses
        </a>
    </div>
</section>

<!-- Categories -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-2xl font-bold text-center mb-8">Explore Categories</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($categories as $category)
            <a href="{{ route('courses.index', ['category' => $category->id]) }}" class="bg-white p-6 rounded-lg text-center hover:shadow-lg transition border">
                <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="{{ $category->icon ?? 'fas fa-book' }} text-teal-600"></i>
                </div>
                <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                <p class="text-sm text-gray-500">{{ $category->courses_count }} Courses</p>
            </a>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Courses -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center mb-8">
            <h2 class="text-2xl font-bold">Featured Courses</h2>
            <a href="{{ route('courses.index') }}" class="btn-3d btn-3d-teal">View All →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($featuredCourses as $course)
            @include('components.course-card', ['course' => $course])
            @endforeach
        </div>
    </div>
</section>

<!-- Popular Courses -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-2xl font-bold text-center mb-8">Most Popular Courses</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($popularCourses as $course)
            @include('components.course-card', ['course' => $course])
            @endforeach
        </div>
    </div>
</section>

<!-- Instructors -->
<section class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-2xl font-bold text-center mb-8">Our Top Instructors</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
            @foreach($topInstructors as $instructor)
            <a href="{{ route('instructors.profile', $instructor) }}" class="text-center group">
                <img src="{{ $instructor->avatar_url }}" alt="{{ $instructor->name }}" class="w-20 h-20 rounded-full mx-auto mb-3 group-hover:ring-4 ring-teal-500 transition">
                <h3 class="font-semibold text-gray-900 text-sm">{{ $instructor->name }}</h3>
                <p class="text-xs text-gray-500">{{ $instructor->courses_count }} Courses</p>
            </a>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA with Background Image -->
<section class="relative bg-cover bg-center bg-no-repeat py-20" style="background-image: linear-gradient(rgba(13, 148, 136, 0.9), rgba(13, 148, 136, 0.9)), url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=1920&q=80');">
    <div class="max-w-4xl mx-auto px-4 text-center text-white">
        <h2 class="text-3xl font-bold mb-4">Ready to Start Learning?</h2>
        <p class="text-teal-100 mb-6 text-lg">Join our community and start your learning journey today.</p>
        <a href="{{ route('register') }}" class="btn-3d btn-3d-white text-lg">
            Get Started Free
        </a>
    </div>
</section>
@endsection
