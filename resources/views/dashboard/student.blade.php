@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Welcome Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ auth()->user()->name }}!</h1>
        <p class="text-gray-600 mt-2">Continue your learning journey</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-book text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Enrolled Courses</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['enrolled_courses'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Completed</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['completed_courses'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-full">
                    <i class="fas fa-certificate text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Certificates</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['certificates'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-clock text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Learning Hours</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_hours'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Continue Learning -->
        <div class="lg:col-span-2">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Continue Learning</h2>
                <a href="{{ route('my.courses') }}" class="text-blue-600 hover:text-blue-700 font-medium">View All</a>
            </div>

            @if($enrollments->count() > 0)
                <div class="space-y-4">
                    @foreach($enrollments as $enrollment)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start space-x-4">
                            <img src="{{ $enrollment->course->thumbnail_url }}" alt="{{ $enrollment->course->title }}" class="w-20 h-20 rounded-lg object-cover">
                            <div class="flex-1">
                                <h3 class="font-semibold text-lg text-gray-900 mb-2">{{ $enrollment->course->title }}</h3>
                                <p class="text-gray-600 mb-2">{{ $enrollment->course->instructor->name }}</p>
                                
                                <div class="flex items-center mb-3">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2 mr-4">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $enrollment->progress_percentage }}%"></div>
                                    </div>
                                    <span class="text-sm text-gray-600">{{ $enrollment->progress_percentage }}%</span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-500">
                                        Last accessed {{ $enrollment->last_accessed_at ? $enrollment->last_accessed_at->diffForHumans() : 'Never' }}
                                    </span>
                                    <a href="{{ route('courses.learn', $enrollment->course) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                                        Continue
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-book text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No courses yet</h3>
                    <p class="text-gray-600 mb-4">Start your learning journey by enrolling in a course</p>
                    <a href="{{ route('courses.index') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                        Browse Courses
                    </a>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <!-- Wishlist -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Wishlist</h3>
                    <a href="{{ route('my.wishlist') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">View All</a>
                </div>

                @if($wishlist->count() > 0)
                    <div class="space-y-3">
                        @foreach($wishlist->take(3) as $course)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                            <div class="flex space-x-3">
                                <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="w-12 h-12 rounded object-cover">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-sm text-gray-900 line-clamp-2">{{ $course->title }}</h4>
                                    <p class="text-xs text-gray-600">{{ $course->instructor->name }}</p>
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="text-sm font-semibold text-gray-900">
                                            {{ $course->isFree() ? 'Free' : '$' . $course->current_price }}
                                        </span>
                                        <a href="{{ route('courses.show', $course) }}" class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                        <i class="fas fa-heart text-gray-300 text-2xl mb-2"></i>
                        <p class="text-gray-600 text-sm">No courses in wishlist</p>
                    </div>
                @endif
            </div>

            <!-- Recent Activity -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                
                @if($recentActivity->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentActivity as $activity)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check text-green-600 text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900">Completed lesson</p>
                                    <p class="text-xs text-gray-600 line-clamp-1">{{ $activity->lesson->title }}</p>
                                    <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 text-center">
                        <i class="fas fa-clock text-gray-300 text-2xl mb-2"></i>
                        <p class="text-gray-600 text-sm">No recent activity</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection