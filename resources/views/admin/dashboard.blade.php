@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 myanmar-text">Admin Dashboard</h1>
        <p class="text-gray-600 mt-2 myanmar-text">Sanpya Online Academy ကို စီမံခန့်ခွဲပါ</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 myanmar-text">အသုံးပြုသူများ</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_users']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-full">
                    <i class="fas fa-book text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 myanmar-text">သင်ခန်းစာများ</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_courses']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-full">
                    <i class="fas fa-graduation-cap text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 myanmar-text">စာရင်းသွင်းမှုများ</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_enrollments']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-full">
                    <i class="fas fa-money-bill text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 myanmar-text">စုစုပေါင်းဝင်ငွေ</p>
                    <p class="text-2xl font-bold text-gray-900">{{ \App\Helpers\CurrencyHelper::formatMMK($stats['total_revenue']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-full">
                    <i class="fas fa-clock text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 myanmar-text">စောင့်ဆိုင်းနေသော</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['pending_courses']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-indigo-100 rounded-full">
                    <i class="fas fa-chalkboard-teacher text-indigo-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600 myanmar-text">ဆရာများ</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['active_instructors']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Enrollments -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 myanmar-text">လတ်တလောစာရင်းသွင်းမှုများ</h2>
            
            @if($recentEnrollments->count() > 0)
                <div class="space-y-4">
                    @foreach($recentEnrollments as $enrollment)
                    <div class="flex items-center space-x-4">
                        <img src="{{ $enrollment->user->avatar_url }}" alt="{{ $enrollment->user->name }}" class="w-10 h-10 rounded-full">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 myanmar-text">{{ $enrollment->user->name }}</p>
                            <p class="text-sm text-gray-600 myanmar-text">{{ $enrollment->course->title }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-900">{{ \App\Helpers\CurrencyHelper::formatMMK($enrollment->price_paid) }}</p>
                            <p class="text-sm text-gray-600">{{ $enrollment->enrolled_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-600 myanmar-text">စာရင်းသွင်းမှုများ မရှိသေးပါ</p>
            @endif
        </div>

        <!-- Top Courses -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4 myanmar-text">ထိပ်တန်းသင်ခန်းစာများ</h2>
            
            @if($topCourses->count() > 0)
                <div class="space-y-4">
                    @foreach($topCourses as $course)
                    <div class="flex items-center space-x-4">
                        <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="w-12 h-12 rounded object-cover">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900 myanmar-text">{{ $course->title }}</p>
                            <p class="text-sm text-gray-600 myanmar-text">{{ $course->instructor->name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-900">{{ $course->enrollments_count }} ကျောင်းသား</p>
                            <p class="text-sm text-gray-600">{{ \App\Helpers\CurrencyHelper::formatMMK($course->price) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-600 myanmar-text">သင်ခန်းစာများ မရှိသေးပါ</p>
            @endif
        </div>
    </div>
</div>
@endsection