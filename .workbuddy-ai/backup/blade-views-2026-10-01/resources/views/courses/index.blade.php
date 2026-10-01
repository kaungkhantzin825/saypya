@extends('layouts.app')

@section('title', 'All Courses')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">All Courses</h1>
            <p class="text-gray-600">Find the best courses for you</p>
        </div>

        <!-- Filters -->
        <style>
            .unified-search-bar {
                display: flex;
                flex-direction: column;
                background-color: transparent;
                gap: 1rem;
            }
            .search-segment {
                flex: 1;
                min-width: 150px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                padding: 0 1rem;
                border-left: 1px solid transparent;
            }
            .search-input {
                width: 100%;
                background: transparent;
                border: none;
                outline: none;
                color: #1f2937;
                font-size: 0.95rem;
                padding: 0;
            }
            .search-label {
                font-size: 0.75rem;
                font-weight: 700;
                color: #374151;
                margin-bottom: 0.1rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }
            @media (min-width: 1024px) {
                .unified-search-bar {
                    flex-direction: row;
                    background-color: white;
                    border-radius: 9999px;
                    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
                    border: 1px solid #f3f4f6;
                    padding: 0.5rem;
                    align-items: center;
                    gap: 0;
                }
                .search-segment:not(:first-child) {
                    border-left-color: #e5e7eb;
                }
                .mobile-bg {
                    background: transparent;
                    border: none;
                    box-shadow: none;
                }
            }
            @media (max-width: 1023px) {
                .search-segment {
                    background: white;
                    padding: 0.75rem 1.25rem;
                    border-radius: 1rem;
                    border: 1px solid #e5e7eb;
                    min-height: 60px;
                }
                .submit-segment {
                    width: 100%;
                }
            }
            select.search-input {
                appearance: none;
                cursor: pointer;
            }
        </style>
        
        <div class="mb-10">
            <form method="GET" action="{{ route('courses.index') }}" class="unified-search-bar">
                
                {{-- Search --}}
                <div class="search-segment" style="flex: 1.5;">
                    <label class="search-label">Course</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="What do you want to learn?" class="search-input">
                </div>

                {{-- Category --}}
                <div class="search-segment">
                    <label class="search-label">Category</label>
                    <select name="category" class="search-input text-gray-500">
                        <option value="">Any Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Level --}}
                <div class="search-segment">
                    <label class="search-label">Level</label>
                    <select name="level" class="search-input text-gray-500">
                        <option value="">Any Level</option>
                        <option value="beginner" {{ request('level') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="intermediate" {{ request('level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="advanced" {{ request('level') == 'advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                </div>

                {{-- Sort --}}
                <div class="search-segment">
                    <label class="search-label">Sort</label>
                    <select name="sort" class="search-input text-gray-500">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Lowest Price</option>
                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Highest Price</option>
                    </select>
                </div>

                {{-- Button --}}
                <div class="submit-segment lg:ml-2">
                    <button type="submit" class="btn-3d btn-3d-teal w-full lg:w-auto h-[50px] lg:h-12 px-8 rounded-full flex items-center justify-center font-bold text-base tracking-wide" style="border-radius: 9999px;">
                        <i class="fas fa-search lg:mr-0 xl:mr-2"></i>
                        <span class="inline lg:hidden xl:inline">Search</span>
                    </button>
                </div>
                
            </form>
        </div>

        <!-- Results -->
        <p class="text-gray-600 mb-4">{{ $courses->total() }} courses found</p>

        <!-- Courses Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            @forelse($courses as $course)
                @include('components.course-card', ['course' => $course])
            @empty
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-search text-gray-300 text-5xl mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No courses found</h3>
                    <p class="text-gray-600">Try adjusting your filters</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($courses->hasPages())
            {{ $courses->appends(request()->query())->links() }}
        @endif
    </div>
</div>
@endsection
