@extends('layouts.app')

@section('title', $category->name . ' Courses')

@section('content')
<!-- Category Header -->
<section class="bg-gradient-to-r from-teal-600 to-cyan-600 text-white py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center mb-4">
            @if($category->icon)
                <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4">
                    <i class="{{ $category->icon }} text-3xl"></i>
                </div>
            @endif
            <div>
                <h1 class="text-4xl font-bold mb-2">{{ $category->name }}</h1>
                @if($category->description)
                <p class="text-teal-100 text-lg">{{ $category->description }}</p>
                @endif
            </div>
        </div>
        <div class="flex items-center space-x-6 text-teal-100">
            <span><i class="fas fa-book mr-2"></i>{{ $courses->total() }} {{ Str::plural('Course', $courses->total()) }}</span>
            <span><i class="fas fa-users mr-2"></i>{{ number_format($totalStudents ?? 0) }} Students</span>
        </div>
    </div>
</section>

<!-- Filters & Courses -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex flex-wrap items-center justify-between mb-8">
            <div>
                <p class="text-gray-600">Showing {{ $courses->firstItem() ?? 0 }} - {{ $courses->lastItem() ?? 0 }} of {{ $courses->total() }} courses</p>
            </div>
            
            <!-- Filters -->
            <form method="GET" class="flex items-center space-x-4">
                <select name="level" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-teal-500 focus:border-teal-500" onchange="this.form.submit()">
                    <option value="">All Levels</option>
                    <option value="beginner" {{ request('level') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                    <option value="intermediate" {{ request('level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                    <option value="advanced" {{ request('level') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                </select>
                
                <select name="sort" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-teal-500 focus:border-teal-500" onchange="this.form.submit()">
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest</option>
                    <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                </select>
            </form>
        </div>

        <!-- Courses Grid -->
        @if($courses->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-8">
            @foreach($courses as $course)
                <x-course-card :course="$course" />
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            {{ $courses->withQueryString()->links() }}
        </div>
        @else
        <div class="text-center py-16">
            <i class="fas fa-book-open text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-2xl font-bold text-gray-700 mb-2">No Courses Found</h3>
            <p class="text-gray-500 mb-6">There are no courses in this category yet.</p>
            <a href="{{ route('categories.index') }}" class="btn-3d btn-3d-teal">
                Browse All Categories
            </a>
        </div>
        @endif
    </div>
</section>

<!-- Other Categories -->
@if($otherCategories->count() > 0)
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-center mb-12">Explore Other Categories</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @foreach($otherCategories as $cat)
            <a href="{{ route('categories.show', $cat->slug) }}" class="bg-white rounded-lg shadow hover:shadow-lg transition p-4 text-center">
                @if($cat->icon)
                <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <i class="{{ $cat->icon }} text-xl text-teal-600"></i>
                </div>
                @endif
                <h4 class="font-semibold text-sm text-gray-800">{{ $cat->name }}</h4>
                <p class="text-xs text-gray-500">{{ $cat->courses_count ?? 0 }} courses</p>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
