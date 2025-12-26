@extends('layouts.app')

@section('title', __('app.hero_title'))

@section('content')
<!-- Hero Section -->
<section class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h1 class="text-4xl md:text-6xl font-bold leading-tight mb-6 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">
                    {!! __('app.hero_title') !!}
                </h1>
                <p class="text-xl text-blue-100 mb-8 leading-relaxed {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">
                    {{ __('app.hero_subtitle') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('courses.index') }}" class="bg-yellow-400 text-gray-900 px-8 py-4 rounded-lg font-semibold hover:bg-yellow-300 transition-colors text-center {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">
                        {{ __('app.browse_courses') }}
                    </a>
                    <a href="{{ route('register') }}" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors text-center {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">
                        {{ __('app.start_learning_free') }}
                    </a>
                </div>
            </div>
            <div class="relative">
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-8">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="bg-white/20 rounded-lg p-4">
                            <div class="text-3xl font-bold">{{ number_format($stats['total_courses']) }}+</div>
                            <div class="text-blue-100 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.courses_count') }}</div>
                        </div>
                        <div class="bg-white/20 rounded-lg p-4">
                            <div class="text-3xl font-bold">{{ number_format($stats['total_students']) }}+</div>
                            <div class="text-blue-100 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.students_count') }}</div>
                        </div>
                        <div class="bg-white/20 rounded-lg p-4">
                            <div class="text-3xl font-bold">{{ number_format($stats['total_instructors']) }}+</div>
                            <div class="text-blue-100 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.instructors_count') }}</div>
                        </div>
                        <div class="bg-white/20 rounded-lg p-4">
                            <div class="text-3xl font-bold">{{ number_format($stats['total_enrollments']) }}+</div>
                            <div class="text-blue-100 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.enrollments_count') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.explore_categories') }}</h2>
            <p class="text-xl text-gray-600 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.categories_subtitle') }}</p>
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @foreach($categories as $category)
            <a href="{{ route('courses.index', ['category' => $category->id]) }}" 
               class="group bg-gray-50 rounded-xl p-6 text-center hover:bg-blue-50 hover:shadow-lg transition-all duration-300">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                    <i class="{{ $category->icon ?? 'fas fa-book' }} text-white text-xl"></i>
                </div>
                <h3 class="font-semibold text-gray-900 mb-2 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ $category->name }}</h3>
                <p class="text-sm text-gray-600 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ $category->courses_count }} {{ __('app.courses_count') }}</p>
            </a>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Courses -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-12">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.featured_courses') }}</h2>
                <p class="text-xl text-gray-600 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.featured_subtitle') }}</p>
            </div>
            <a href="{{ route('courses.index') }}" class="text-blue-600 hover:text-blue-700 font-semibold {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">
                {{ __('app.view_all_courses') }} <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($featuredCourses as $course)
            <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                <div class="relative">
                    <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="w-full h-48 object-cover">
                    <div class="absolute top-3 left-3">
                        <span class="bg-yellow-400 text-gray-900 px-2 py-1 rounded-full text-xs font-semibold {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.featured') }}</span>
                    </div>
                    @if($course->hasDiscount())
                    <div class="absolute top-3 right-3">
                        <span class="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                            {{ $course->discount_percentage }}% {{ __('app.discount') }}
                        </span>
                    </div>
                    @endif
                </div>
                
                <div class="p-6">
                    <div class="flex items-center mb-2">
                        <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ $course->category->name }}</span>
                        <span class="text-xs text-gray-500 ml-2 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.' . $course->level) }}</span>
                    </div>
                    
                    <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ $course->title }}</h3>
                    
                    <div class="flex items-center mb-3">
                        <img src="{{ $course->instructor->avatar_url }}" alt="{{ $course->instructor->name }}" class="w-6 h-6 rounded-full mr-2">
                        <span class="text-sm text-gray-600 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ $course->instructor->name }}</span>
                    </div>
                    
                    <div class="flex items-center mb-3">
                        <div class="flex items-center mr-4">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star text-xs {{ $i <= $course->average_rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                            @endfor
                            <span class="text-sm text-gray-600 ml-1">({{ $course->total_reviews }})</span>
                        </div>
                        <span class="text-sm text-gray-600 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ $course->total_students }} {{ __('app.students') }}</span>
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
                        <a href="{{ route('courses.show', $course) }}" class="text-blue-600 hover:text-blue-700 font-semibold text-sm {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">
                            {{ __('app.view_course') }}
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Popular Courses -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.popular_courses') }}</h2>
            <p class="text-xl text-gray-600 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.popular_subtitle') }}</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($popularCourses as $course)
            <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden border border-gray-100">
                <div class="relative">
                    <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="w-full h-48 object-cover">
                    <div class="absolute top-3 left-3">
                        <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-semibold {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.popular') }}</span>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="flex items-center mb-2">
                        <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ $course->category->name }}</span>
                        <span class="text-xs text-gray-500 ml-2 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.' . $course->level) }}</span>
                    </div>
                    
                    <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ $course->title }}</h3>
                    
                    <div class="flex items-center mb-3">
                        <img src="{{ $course->instructor->avatar_url }}" alt="{{ $course->instructor->name }}" class="w-6 h-6 rounded-full mr-2">
                        <span class="text-sm text-gray-600 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ $course->instructor->name }}</span>
                    </div>
                    
                    <div class="flex items-center mb-3">
                        <div class="flex items-center mr-4">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star text-xs {{ $i <= $course->average_rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                            @endfor
                            <span class="text-sm text-gray-600 ml-1">({{ $course->total_reviews }})</span>
                        </div>
                        <span class="text-sm text-gray-600 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ $course->total_students }} {{ __('app.students') }}</span>
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
                        <a href="{{ route('courses.show', $course) }}" class="text-blue-600 hover:text-blue-700 font-semibold text-sm {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">
                            {{ __('app.view_course') }}
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Top Instructors -->
<section class="py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.top_instructors') }}</h2>
            <p class="text-xl text-gray-600 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.instructors_subtitle') }}</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($topInstructors as $instructor)
            <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 p-6 text-center">
                <img src="{{ $instructor->avatar_url }}" alt="{{ $instructor->name }}" class="w-20 h-20 rounded-full mx-auto mb-4">
                <h3 class="font-semibold text-gray-900 mb-2 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ $instructor->name }}</h3>
                <p class="text-gray-600 mb-4 line-clamp-2 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ $instructor->bio ?? (app()->getLocale() == 'my' ? 'အတွေ့အကြုံရှိသော ဆရာ၊ သင်ကြားခြင်းနှင့် အသိပညာမျှဝေခြင်းကို နှစ်သက်သူ။' : 'Experienced instructor passionate about teaching and sharing knowledge.') }}</p>
                <div class="flex justify-center space-x-4 text-sm text-gray-600 mb-4">
                    <span class="{{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}"><i class="fas fa-book mr-1"></i>{{ $instructor->courses_count }} {{ __('app.courses_count') }}</span>
                    <span class="{{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}"><i class="fas fa-users mr-1"></i>{{ $instructor->courses->sum('total_students') }} {{ __('app.students') }}</span>
                </div>
                <a href="{{ route('instructors.profile', $instructor) }}" class="text-blue-600 hover:text-blue-700 font-semibold {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.view_profile') }}</a>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-16 bg-gradient-to-r from-blue-600 to-purple-600 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl md:text-4xl font-bold mb-6 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.cta_title') }}</h2>
        <p class="text-xl text-blue-100 mb-8 {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">{{ __('app.cta_subtitle') }}</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" class="bg-yellow-400 text-gray-900 px-8 py-4 rounded-lg font-semibold hover:bg-yellow-300 transition-colors {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">
                {{ __('app.get_started_free') }}
            </a>
            <a href="{{ route('courses.index') }}" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition-colors {{ app()->getLocale() == 'my' ? 'myanmar-text' : '' }}">
                {{ __('app.browse_all_courses') }}
            </a>
        </div>
    </div>
</section>
@endsection