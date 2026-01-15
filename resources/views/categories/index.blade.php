@extends('layouts.app')

@section('title', 'All Categories')

@section('content')
<!-- Page Header -->
<section class="bg-gradient-to-r from-teal-600 to-cyan-600 text-white py-12">
    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-4xl font-bold mb-2">Browse Categories</h1>
        <p class="text-teal-100">Explore courses by category</p>
    </div>
</section>

<!-- Categories Grid -->
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($categories as $category)
            <a href="{{ route('categories.show', $category->slug) }}" class="bg-white rounded-lg shadow-lg hover:shadow-xl transition-all p-6 text-center group">
                <div class="w-20 h-20 bg-gradient-to-br from-teal-400 to-cyan-500 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    @if($category->icon)
                        <i class="{{ $category->icon }} text-3xl text-white"></i>
                    @else
                        <i class="fas fa-folder text-3xl text-white"></i>
                    @endif
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-teal-600">{{ $category->name }}</h3>
                @if($category->description)
                <p class="text-gray-600 text-sm mb-3">{{ Str::limit($category->description, 80) }}</p>
                @endif
                <p class="text-teal-600 font-semibold">
                    {{ $category->courses_count ?? $category->courses->count() }} 
                    {{ Str::plural('Course', $category->courses_count ?? $category->courses->count()) }}
                </p>
            </a>
            @empty
            <div class="col-span-full text-center py-12">
                <i class="fas fa-folder-open text-6xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">No categories available yet.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Popular Courses -->
@if(isset($popularCourses) && $popularCourses->count() > 0)
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Popular Courses</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($popularCourses as $course)
                <x-course-card :course="$course" />
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
