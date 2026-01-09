{{-- Clean Course Card Design with 3D Button --}}
<div class="bg-white rounded-lg shadow hover:shadow-xl transition overflow-hidden border border-gray-100">
    {{-- Thumbnail --}}
    <div class="relative bg-gray-100 h-48 flex items-center justify-center p-4">
        <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="max-h-full max-w-full object-contain">
        
        {{-- Level Badge --}}
        <div class="absolute top-3 right-3">
            @php
                $levelStyles = match($course->level) {
                    'beginner' => 'background: linear-gradient(180deg, #48bb78 0%, #38a169 50%, #2f855a 100%); box-shadow: 0 3px 0 #276749;',
                    'intermediate' => 'background: linear-gradient(180deg, #ed8936 0%, #dd6b20 50%, #c05621 100%); box-shadow: 0 3px 0 #9c4221;',
                    'advanced' => 'background: linear-gradient(180deg, #fc8181 0%, #e53e3e 50%, #c53030 100%); box-shadow: 0 3px 0 #9b2c2c;',
                    default => 'background: linear-gradient(180deg, #4fd1c5 0%, #319795 50%, #2c7a7b 100%); box-shadow: 0 3px 0 #285e61;'
                };
            @endphp
            <span class="text-white text-xs font-bold px-4 py-2 rounded-md inline-block" style="{{ $levelStyles }}">
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
