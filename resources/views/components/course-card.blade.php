{{-- Clean Course Card Design with 3D Button --}}
<div class="bg-white rounded-lg shadow hover:shadow-xl transition overflow-hidden border border-gray-100">
    {{-- Thumbnail --}}
    <div class="relative bg-gray-100 h-48 flex items-center justify-center p-4">
        <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="max-h-full max-w-full object-contain">
        
        {{-- Level Badge --}}
        <div class="absolute top-0 right-0">
            @php
                $levelColor = match($course->level) {
                    'beginner' => 'bg-green-600',
                    'intermediate' => 'bg-orange-500',
                    'advanced' => 'bg-red-600',
                    default => 'bg-teal-600'
                };
            @endphp
            <span class="{{ $levelColor }} text-white text-xs font-semibold px-3 py-1">
                {{ ucfirst($course->level) }}
            </span>
        </div>
    </div>
    
    {{-- Content --}}
    <div class="p-4">
        {{-- Title --}}
        <h3 class="font-bold text-gray-900 mb-2 line-clamp-2 h-12">{{ $course->title }}</h3>
        
        {{-- Description --}}
        @if($course->short_description)
        <p class="text-red-600 text-sm mb-3 line-clamp-2">{{ $course->short_description }}</p>
        @endif
        
        {{-- Info --}}
        <div class="text-sm text-gray-600 space-y-1 mb-3">
            <div><i class="fas fa-clock w-5 text-gray-400"></i> {{ $course->duration_hours ?: 'In Progress' }} hours</div>
            <div><i class="fas fa-book w-5 text-gray-400"></i> {{ $course->lessons_count ?? '10+' }} lessons</div>
        </div>
        
        {{-- Price --}}
        <div class="border-t pt-3 mb-4">
            <div class="flex justify-between text-sm">
                <span class="text-gray-600">Price:</span>
                <span class="font-bold text-gray-900">{{ \App\Helpers\CurrencyHelper::formatMMK($course->price) }}</span>
            </div>
        </div>
        
        {{-- 3D Button --}}
        <a href="{{ route('courses.show', $course) }}" class="btn-3d btn-3d-cyan block text-center w-full">
            LEARN NOW
        </a>
    </div>
</div>
