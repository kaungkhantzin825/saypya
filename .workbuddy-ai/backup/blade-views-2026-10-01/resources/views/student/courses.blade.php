@extends('layouts.app')

@section('title', 'ကျွန်ုပ်၏သင်ခန်းစာများ')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 myanmar-text">ကျွန်ုပ်၏သင်ခန်းစာများ</h1>
        <p class="text-gray-600 mt-2 myanmar-text">သင်စာရင်းသွင်းထားသော သင်ခန်းစာများ</p>
    </div>

    @if($enrollments->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($enrollments as $enrollment)
            <div class="bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                <div class="relative">
                    <img src="{{ $enrollment->course->thumbnail_url }}" alt="{{ $enrollment->course->title }}" class="w-full h-48 object-cover">
                    <div class="absolute top-3 left-3">
                        <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs font-semibold myanmar-text">
                            စာရင်းသွင်းပြီး
                        </span>
                    </div>
                    <div class="absolute bottom-3 right-3 bg-black bg-opacity-75 text-white px-2 py-1 rounded text-xs">
                        {{ $enrollment->progress_percentage }}% ပြီးစီး
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="flex items-center mb-2">
                        <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full myanmar-text">{{ $enrollment->course->category->name }}</span>
                        <span class="text-xs text-gray-500 ml-2 myanmar-text">{{ ucfirst($enrollment->course->level) }}</span>
                    </div>
                    
                    <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2 myanmar-text">{{ $enrollment->course->title }}</h3>
                    
                    <div class="flex items-center mb-3">
                        <img src="{{ $enrollment->course->instructor->avatar_url }}" alt="{{ $enrollment->course->instructor->name }}" class="w-6 h-6 rounded-full mr-2">
                        <span class="text-sm text-gray-600 myanmar-text">{{ $enrollment->course->instructor->name }}</span>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-gray-600 myanmar-text">တိုးတက်မှု</span>
                            <span class="text-sm text-gray-600">{{ $enrollment->progress_percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $enrollment->progress_percentage }}%"></div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-600 myanmar-text">
                            စာရင်းသွင်းသည့်ရက်: {{ $enrollment->enrolled_at->format('M d, Y') }}
                        </div>
                        <a href="{{ route('courses.learn', $enrollment->course) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors myanmar-text">
                            {{ $enrollment->progress_percentage > 0 ? 'ဆက်လက်သင်ယူရန်' : 'စတင်သင်ယူရန်' }}
                        </a>
                    </div>
                    
                    @if($enrollment->completed_at)
                    <div class="mt-3 pt-3 border-t border-gray-200">
                        <div class="flex items-center text-green-600">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span class="text-sm font-medium myanmar-text">{{ $enrollment->completed_at->format('M d, Y') }} တွင် ပြီးစီး</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($enrollments->hasPages())
        <div class="mt-8 flex justify-center">
            {{ $enrollments->links() }}
        </div>
        @endif
    @else
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-book text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2 myanmar-text">သင်ခန်းစာများ မရှိသေးပါ</h3>
            <p class="text-gray-600 mb-4 myanmar-text">သင်ခန်းစာများကို စာရင်းသွင်းပြီး သင်ယူခြင်းကို စတင်ပါ</p>
            <a href="{{ route('courses.index') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors myanmar-text">
                သင်ခန်းစာများကြည့်ရှုရန်
            </a>
        </div>
    @endif
</div>
@endsection