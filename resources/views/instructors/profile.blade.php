@extends('layouts.app')

@section('title', $instructor->name . ' - ' . __('app.instructor') . ' ' . __('app.profile'))

@section('content')
<!-- Instructor Header -->
<div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="flex flex-col md:flex-row items-center md:items-start space-y-6 md:space-y-0 md:space-x-8">
            <img src="{{ $instructor->avatar_url }}" alt="{{ $instructor->name }}" class="w-32 h-32 rounded-full border-4 border-white shadow-lg">
            
            <div class="text-center md:text-left flex-1">
                <h1 class="text-4xl font-bold mb-2 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ $instructor->name }}</h1>
                <p class="text-xl text-blue-100 mb-4 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.instructor') }}</p>
                
                @if($instructor->bio)
                <p class="text-blue-100 mb-6 max-w-2xl {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ $instructor->bio }}</p>
                @endif
                
                <!-- Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold">{{ number_format($stats['total_courses']) }}</div>
                        <div class="text-blue-100 text-sm {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.courses_count') }}</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold">{{ number_format($stats['total_students']) }}</div>
                        <div class="text-blue-100 text-sm {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.students_count') }}</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold">{{ number_format($stats['average_rating'], 1) }}</div>
                        <div class="text-blue-100 text-sm {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.average_rating') }}</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold">{{ number_format($stats['total_reviews']) }}</div>
                        <div class="text-blue-100 text-sm {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.total_reviews') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Content -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- About Section -->
            @if($instructor->bio)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4 myanmar-text">အကြောင်းအရာ</h2>
                <p class="text-gray-700 leading-relaxed myanmar-text">{{ $instructor->bio }}</p>
            </div>
            @endif

            <!-- Courses Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 myanmar-text">သင်ခန်းစာများ</h2>
                    <span class="text-gray-600 myanmar-text">{{ $courses->total() }} သင်ခန်းစာ</span>
                </div>

                @if($courses->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($courses as $course)
                        <div class="bg-gray-50 rounded-lg overflow-hidden hover:shadow-md transition-shadow">
                            <div class="relative">
                                <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="w-full h-40 object-cover">
                                @if($course->hasDiscount())
                                <div class="absolute top-2 right-2">
                                    <span class="bg-red-500 text-white px-2 py-1 rounded text-xs font-semibold">
                                        {{ $course->discount_percentage }}% လျှော့စျေး
                                    </span>
                                </div>
                                @endif
                            </div>
                            
                            <div class="p-4">
                                <div class="flex items-center mb-2">
                                    <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded myanmar-text">{{ $course->category->name }}</span>
                                    <span class="text-xs text-gray-500 ml-2 myanmar-text">{{ ucfirst($course->level) }}</span>
                                </div>
                                
                                <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 myanmar-text">{{ $course->title }}</h3>
                                
                                <div class="flex items-center mb-3">
                                    <div class="flex items-center mr-4">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star text-xs {{ $i <= $course->average_rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                        @endfor
                                        <span class="text-sm text-gray-600 ml-1">({{ $course->total_reviews }})</span>
                                    </div>
                                    <span class="text-sm text-gray-600 myanmar-text">{{ $course->total_students }} ကျောင်းသား</span>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        @if($course->hasDiscount())
                                            <span class="text-lg font-bold text-gray-900">{{ \App\Helpers\CurrencyHelper::formatMMK($course->discount_price) }}</span>
                                            <span class="text-sm text-gray-500 line-through ml-2">{{ \App\Helpers\CurrencyHelper::formatMMK($course->price) }}</span>
                                        @else
                                            <span class="text-lg font-bold text-gray-900">
                                                {{ \App\Helpers\CurrencyHelper::formatMMK($course->price) }}
                                            </span>
                                        @endif
                                    </div>
                                    <a href="{{ route('courses.show', $course) }}" class="text-blue-600 hover:text-blue-700 font-semibold text-sm myanmar-text">
                                        ကြည့်ရှုရန်
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($courses->hasPages())
                    <div class="mt-8 flex justify-center">
                        {{ $courses->links() }}
                    </div>
                    @endif
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-book text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600 myanmar-text">ဤဆရာတွင် သင်ခန်းစာများ မရှိသေးပါ</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <!-- Contact Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 myanmar-text">အချက်အလက်များ</h3>
                
                <div class="space-y-3">
                    @if($instructor->country)
                    <div class="flex items-center">
                        <i class="fas fa-map-marker-alt text-gray-400 w-5"></i>
                        <span class="text-gray-700 ml-3 myanmar-text">{{ $instructor->country }}</span>
                    </div>
                    @endif
                    
                    <div class="flex items-center">
                        <i class="fas fa-calendar text-gray-400 w-5"></i>
                        <span class="text-gray-700 ml-3 myanmar-text">{{ $instructor->created_at->format('Y') }} ခုနှစ်မှစ၍ ပါဝင်</span>
                    </div>
                    
                    @if($instructor->last_login_at)
                    <div class="flex items-center">
                        <i class="fas fa-clock text-gray-400 w-5"></i>
                        <span class="text-gray-700 ml-3 myanmar-text">{{ $instructor->last_login_at->diffForHumans() }} တွင် လှုပ်ရှားခဲ့</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Recent Reviews -->
            @if($recentReviews->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 myanmar-text">လတ်တလောသုံးသပ်ချက်များ</h3>
                
                <div class="space-y-4">
                    @foreach($recentReviews as $review)
                    <div class="border-b border-gray-200 pb-4 last:border-b-0 last:pb-0">
                        <div class="flex items-start space-x-3">
                            <img src="{{ $review->user->avatar_url }}" alt="{{ $review->user->name }}" class="w-8 h-8 rounded-full">
                            <div class="flex-1">
                                <div class="flex items-center mb-1">
                                    <span class="font-medium text-gray-900 text-sm myanmar-text">{{ $review->user->name }}</span>
                                    <div class="flex items-center ml-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star text-xs {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                @if($review->comment)
                                <p class="text-gray-600 text-sm line-clamp-2 myanmar-text">{{ $review->comment }}</p>
                                @endif
                                <p class="text-xs text-gray-500 mt-1 myanmar-text">{{ $review->course->title }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection