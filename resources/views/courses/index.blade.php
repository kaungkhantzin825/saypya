@extends('layouts.app')

@section('title', 'သင်ခန်းစာများ')

@section('content')
<div class="bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-4 myanmar-text">သင်ခန်းစာများ</h1>
            <p class="text-gray-600 myanmar-text">သင့်အတွက် အကောင်းဆုံးသင်ခန်းစာများကို ရှာဖွေပါ</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
            <form method="GET" action="{{ route('courses.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">အမျိုးအစား</label>
                    <select name="category" id="category" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="" class="myanmar-text">အားလုံး</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }} class="myanmar-text">
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">အဆင့်</label>
                    <select name="level" id="level" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="" class="myanmar-text">အားလုံး</option>
                        <option value="beginner" {{ request('level') == 'beginner' ? 'selected' : '' }} class="myanmar-text">အစပိုင်း</option>
                        <option value="intermediate" {{ request('level') == 'intermediate' ? 'selected' : '' }} class="myanmar-text">အလယ်အလတ်</option>
                        <option value="advanced" {{ request('level') == 'advanced' ? 'selected' : '' }} class="myanmar-text">အဆင့်မြင့်</option>
                    </select>
                </div>

                <div>
                    <label for="sort" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">စီစဉ်ပုံ</label>
                    <select name="sort" id="sort" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }} class="myanmar-text">အသစ်ဆုံး</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }} class="myanmar-text">အဟောင်းဆုံး</option>
                        <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }} class="myanmar-text">လူကြိုက်များ</option>
                        <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }} class="myanmar-text">အဆင့်သတ်မှတ်ချက်</option>
                        <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }} class="myanmar-text">စျေးနှုန်း (နည်း)</option>
                        <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }} class="myanmar-text">စျေးနှုန်း (များ)</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors myanmar-text">
                        စစ်ထုတ်ရန်
                    </button>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div class="flex justify-between items-center mb-6">
            <p class="text-gray-600 myanmar-text">{{ $courses->total() }} သင်ခန်းစာတွေ့ရှိသည်</p>
        </div>

        <!-- Courses Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
            @forelse($courses as $course)
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
                        <i class="{{ auth()->check() && $course->isInWishlistOf(auth()->id()) ? 'fas fa-heart text-red-500' : 'far fa-heart text-gray-600' }}"></i>
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
            @empty
            <div class="col-span-full text-center py-12">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-search text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2 myanmar-text">သင်ခန်းစာများ မတွေ့ရှိပါ</h3>
                <p class="text-gray-600 myanmar-text">ရှာဖွေမှုစံနှုန်းများကို ပြောင်းလဲကြည့်ပါ</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($courses->hasPages())
        <div class="flex justify-center">
            {{ $courses->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection