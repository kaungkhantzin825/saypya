{{-- Modern Course Card Design --}}
<div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-100 flex flex-col h-full group relative">
    {{-- Thumbnail --}}
    <div class="relative h-56 overflow-hidden">
        <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
        
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/60 to-transparent"></div>
        
        {{-- Tags/Badges --}}
        <div class="absolute top-4 left-4 right-4 flex justify-between items-start">
            {{-- Category (if available) --}}
            @if($course->category)
            <span class="bg-white/90 backdrop-blur-sm text-teal-700 text-xs font-bold px-3 py-1.5 rounded-full shadow-sm myanmar-text uppercase tracking-wider">
                {{ $course->category->name }}
            </span>
            @else
            <span></span>
            @endif

            {{-- Level Badge --}}
            @php
                $levelStyles = match($course->level) {
                    'beginner' => 'bg-green-500 text-white',
                    'intermediate' => 'bg-orange-500 text-white',
                    'advanced' => 'bg-red-500 text-white',
                    default => 'bg-teal-500 text-white'
                };
            @endphp
            <span class="{{ $levelStyles }} text-[11px] font-bold px-3 py-1.5 rounded-full shadow-sm uppercase tracking-wider">
                {{ ucfirst($course->level) }}
            </span>
        </div>
    </div>
    
    {{-- Content --}}
    <div class="p-6 flex flex-col flex-grow">
        
        {{-- Title --}}
        <h3 class="font-bold text-xl text-gray-900 mb-2 line-clamp-2 myanmar-text group-hover:text-teal-600 transition-colors leading-snug">
            <a href="{{ route('courses.show', $course) }}" class="focus:outline-none">
                <span class="absolute inset-0" aria-hidden="true"></span>
                {{ $course->title }}
            </a>
        </h3>
        
        {{-- Description --}}
        @if($course->short_description)
        <p class="text-gray-500 text-sm mb-4 line-clamp-2 myanmar-text">{{ $course->short_description }}</p>
        @endif
        
        {{-- Meta Info --}}
        <div class="flex items-center gap-4 text-sm text-gray-500 mt-auto mb-5 font-medium">
            <span class="flex items-center gap-1.5">
                <i class="fas fa-clock text-teal-500 border border-teal-100 bg-teal-50 p-1.5 rounded-md"></i>
                {{ $course->duration_hours ?: 'In Progress' }} hrs
            </span>
            <span class="flex items-center gap-1.5">
                <i class="fas fa-book text-blue-500 border border-blue-100 bg-blue-50 p-1.5 rounded-md"></i>
                {{ $course->lessons_count ?? '10+' }} lessons
            </span>
        </div>
        
        <div class="border-t border-gray-100 pt-5 mt-auto flex items-center justify-between z-10 relative">
            {{-- Price --}}
            <div class="flex flex-col">
                <span class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-0.5">Price</span>
                <span class="font-bold text-lg text-teal-700">
                    {{ $course->price > 0 ? \App\Helpers\CurrencyHelper::formatMMK($course->price) : 'Free' }}
                </span>
            </div>
            
            {{-- Button --}}
            <a href="{{ route('courses.show', $course) }}" class="inline-flex items-center justify-center bg-teal-600 hover:bg-teal-700 text-white font-medium py-2.5 px-5 rounded-xl transition-all duration-200 shadow hover:shadow-md transform hover:-translate-y-0.5">
                Learn Now <i class="fas fa-arrow-right ml-2 text-xs"></i>
            </a>
        </div>
    </div>
</div>
