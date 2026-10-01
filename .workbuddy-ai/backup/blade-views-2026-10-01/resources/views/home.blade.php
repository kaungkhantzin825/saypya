@extends('layouts.app')

@section('title', 'Home')

@section('content')
<!-- Hero Carousel -->
@if($heroSlides->count() > 0)
<section class="relative min-h-[500px] overflow-hidden group" x-data="{
        slides: {{ $heroSlides->count() }},
        current: 0,
        timer: null,
        start() {
            if (this.slides <= 1) return;
            this.timer = setInterval(() => this.next(), 6000);
        },
        restart() {
            clearInterval(this.timer);
            this.start();
        },
        next() { this.current = (this.current + 1) % this.slides; },
        prev() { this.current = (this.current - 1 + this.slides) % this.slides; }
    }" x-init="start()">
    @foreach($heroSlides as $index => $slide)
    @php $hasText = $slide->title || $slide->subtitle || $slide->button_text; @endphp
    <div
        class="absolute inset-0 min-h-[500px]"
        x-show="current === {{ $index }}"
        x-transition:enter="transition-opacity ease-out duration-1000"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-700"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        {{-- Background layer: slow continuous zoom, kept separate from the opacity fade above --}}
        <div
            class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-transform duration-[6000ms] ease-out"
            style="background-image: {{ $hasText ? 'linear-gradient(180deg, rgba(0,0,0,0.25) 0%, rgba(0,0,0,0.45) 55%, rgba(0,0,0,0.7) 100%), ' : '' }}url('{{ $slide->image_url }}');"
            :class="current === {{ $index }} ? 'scale-110' : 'scale-100'"
        ></div>

        @if($hasText)
        <div class="relative min-h-[500px] flex items-center">
            <div
                class="max-w-7xl mx-auto px-4 py-16 text-center text-white w-full"
                x-show="current === {{ $index }}"
                x-transition:enter="transition ease-out duration-700 delay-300"
                x-transition:enter-start="opacity-0 translate-y-6"
                x-transition:enter-end="opacity-100 translate-y-0"
            >
                @if($slide->title)
                    <h1 class="text-4xl md:text-6xl font-extrabold mb-4 drop-shadow-lg tracking-tight">{{ $slide->title }}</h1>
                @endif
                @if($slide->subtitle)
                    <p class="text-xl text-gray-100 mb-8 max-w-2xl mx-auto drop-shadow">{{ $slide->subtitle }}</p>
                @endif
                @if($slide->button_text)
                    <a href="{{ $slide->button_link ?: route('courses.index') }}" class="btn-3d btn-3d-cyan text-lg">
                        {{ $slide->button_text }}
                    </a>
                @endif
            </div>
        </div>
        @endif
    </div>
    @endforeach

    @if($heroSlides->count() > 1)
    <!-- Arrow Navigation -->
    <button
        @click="prev(); restart()"
        class="absolute left-3 md:left-6 top-1/2 -translate-y-1/2 z-20 w-11 h-11 md:w-12 md:h-12 rounded-full bg-white/15 hover:bg-white/30 backdrop-blur-sm border border-white/30 flex items-center justify-center text-white transition-all duration-200 opacity-70 hover:opacity-100 hover:scale-110"
        aria-label="Previous slide"
    >
        <i class="fas fa-chevron-left"></i>
    </button>
    <button
        @click="next(); restart()"
        class="absolute right-3 md:right-6 top-1/2 -translate-y-1/2 z-20 w-11 h-11 md:w-12 md:h-12 rounded-full bg-white/15 hover:bg-white/30 backdrop-blur-sm border border-white/30 flex items-center justify-center text-white transition-all duration-200 opacity-70 hover:opacity-100 hover:scale-110"
        aria-label="Next slide"
    >
        <i class="fas fa-chevron-right"></i>
    </button>

    <!-- Dots -->
    <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex items-center gap-2 z-20">
        @foreach($heroSlides as $index => $slide)
        <button
            class="h-2.5 rounded-full transition-all duration-300"
            :class="current === {{ $index }} ? 'bg-white w-8' : 'bg-white/40 w-2.5 hover:bg-white/70'"
            @click="current = {{ $index }}; restart()"
            aria-label="Go to slide {{ $index + 1 }}"
        ></button>
        @endforeach
    </div>
    @endif
</section>
@endif

<!-- Categories -->
<!-- <section class="py-12 bg-gray-50">
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
</section> -->

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
<!-- <section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-2xl font-bold text-center mb-8">Most Popular Courses</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($popularCourses as $course)
            @include('components.course-card', ['course' => $course])
            @endforeach
        </div>
    </div>
</section> -->

<!-- Instructors -->
<!-- <section class="py-12 bg-white">
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
</section> -->

<!-- CTA with Background Image -->
<section class="relative bg-cover bg-center bg-no-repeat py-20" style="background-image: linear-gradient(rgba(13, 148, 136, 0.9), rgba(13, 148, 136, 0.9)), url('https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=1920&q=80');">
    <div class="max-w-4xl mx-auto px-4 text-center text-white">
        <h2 class="text-3xl font-bold mb-4">Ready to Start Learning?</h2>
        <p class="text-teal-100 mb-6 text-lg">Join our community and start your learning journey today.</p>
        @auth
            <a href="{{ route('courses.index') }}" class="btn-3d btn-3d-white text-lg">
                Browse Courses
            </a>
        @else
            <a href="{{ route('register') }}" class="btn-3d btn-3d-white text-lg">
                Get Started Free
            </a>
        @endauth
    </div>
</section>
@endsection
