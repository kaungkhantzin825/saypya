@extends('layouts.app')

@section('title', 'ကျွန်ုပ်၏ဆန္ဒစာရင်း')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 myanmar-text">ကျွန်ုပ်၏ဆန္ဒစာရင်း</h1>
        <p class="text-gray-600 mt-2 myanmar-text">သင်နှစ်သက်သော သင်ခန်းစာများ</p>
    </div>

    @if($wishlist->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($wishlist as $course)
            <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                <div class="relative">
                    <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="w-full h-48 object-cover">
                    @if($course->is_featured)
                    <div class="absolute top-3 left-3">
                        <span class="bg-yellow-400 text-gray-900 px-2 py-1 rounded-full text-xs font-semibold myanmar-text">ထူးခြား</span>
                    </div>
                    @endif
                    @if($course->hasDiscount())
                    <div class="absolute top-3 right-3">
                        <span class="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-semibold">
                            {{ $course->discount_percentage }}% လျှော့စျေး
                        </span>
                    </div>
                    @endif
                    <button onclick="toggleWishlist({{ $course->id }})" 
                            class="absolute bottom-3 right-3 bg-white bg-opacity-90 hover:bg-opacity-100 rounded-full p-2 transition-all"
                            data-course-id="{{ $course->id }}">
                        <i class="fas fa-heart text-red-500"></i>
                    </button>
                </div>
                
                <div class="p-6">
                    <div class="flex items-center mb-2">
                        <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full myanmar-text">{{ $course->category->name }}</span>
                        <span class="text-xs text-gray-500 ml-2 myanmar-text">{{ ucfirst($course->level) }}</span>
                    </div>
                    
                    <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 myanmar-text">{{ $course->title }}</h3>
                    
                    <div class="flex items-center mb-3">
                        <img src="{{ $course->instructor->avatar_url }}" alt="{{ $course->instructor->name }}" class="w-6 h-6 rounded-full mr-2">
                        <span class="text-sm text-gray-600 myanmar-text">{{ $course->instructor->name }}</span>
                    </div>
                    
                    <div class="flex items-center mb-3">
                        <div class="flex items-center mr-4">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star text-xs {{ $i <= $course->average_rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                            @endfor
                            <span class="text-sm text-gray-600 ml-1">({{ $course->total_reviews }})</span>
                        </div>
                        <span class="text-sm text-gray-600 myanmar-text">{{ $course->total_students }} ကျောင်းသား</span>
                    </div>
                    
                    <div class="flex items-center justify-between mb-4">
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
                    </div>
                    
                    <div class="flex space-x-2">
                        @if(auth()->check() && $course->isEnrolledBy(auth()->id()))
                            <a href="{{ route('courses.learn', $course) }}" class="flex-1 bg-green-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-green-700 transition-colors text-center myanmar-text">
                                သင်ယူရန်
                            </a>
                        @else
                            <a href="{{ route('courses.show', $course) }}" class="flex-1 bg-blue-600 text-white py-2 px-4 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors text-center myanmar-text">
                                စာရင်းသွင်းရန်
                            </a>
                        @endif
                        <a href="{{ route('courses.show', $course) }}" class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors myanmar-text">
                            ကြည့်ရှုရန်
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($wishlist->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $wishlist->links() }}
        </div>
        @endif
    @else
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-heart text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2 myanmar-text">ဆန္ဒစာရင်း ဗလာဖြစ်နေသည်</h3>
            <p class="text-gray-600 mb-4 myanmar-text">သင်နှစ်သက်သော သင်ခန်းစာများကို ဆန္ဒစာရင်းတွင် ထည့်ပါ</p>
            <a href="{{ route('courses.index') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors myanmar-text">
                သင်ခန်းစာများကြည့်ရှုရန်
            </a>
        </div>
    @endif
</div>
@endsection