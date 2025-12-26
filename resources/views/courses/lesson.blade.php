@extends('layouts.app')

@section('title', $lesson->title . ' - ' . $course->title)

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-0">
            <!-- Video Player Area -->
            <div class="lg:col-span-3 bg-black">
                <div class="aspect-video bg-gray-900 flex items-center justify-center">
                    @if($lesson->video_url)
                        <video id="lesson-video" class="w-full h-full" controls>
                            <source src="{{ $lesson->video_url_full }}" type="video/mp4">
                            သင့်ဘရောက်ဆာသည် ဗီဒီယိုကို ပံ့ပိုးမပေးပါ။
                        </video>
                    @else
                        <div class="text-center text-white">
                            <i class="fas fa-play-circle text-6xl mb-4 opacity-50"></i>
                            <p class="text-xl myanmar-text">ဗီဒီယို မရရှိနိုင်ပါ</p>
                        </div>
                    @endif
                </div>
                
                <!-- Lesson Info -->
                <div class="bg-white p-6 border-b">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2 myanmar-text">{{ $lesson->title }}</h1>
                    <div class="flex items-center space-x-4 text-sm text-gray-600">
                        <span class="myanmar-text">{{ $course->title }}</span>
                        <span>•</span>
                        <span class="myanmar-text">{{ $lesson->section->title }}</span>
                        @if($lesson->video_duration)
                        <span>•</span>
                        <span>{{ $lesson->formatted_duration }}</span>
                        @endif
                    </div>
                    @if($lesson->description)
                    <p class="mt-4 text-gray-700 myanmar-text">{{ $lesson->description }}</p>
                    @endif
                    
                    @if($lesson->content)
                    <div class="mt-6 prose max-w-none">
                        {!! nl2br(e($lesson->content)) !!}
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Course Content Sidebar -->
            <div class="lg:col-span-1 bg-white border-l border-gray-200 max-h-screen overflow-y-auto">
                <div class="p-4 border-b border-gray-200">
                    <h2 class="font-semibold text-gray-900 mb-2 myanmar-text">သင်ခန်းစာအကြောင်းအရာ</h2>
                    <div class="text-sm text-gray-600 myanmar-text">
                        {{ $enrollment->progress_percentage }}% ပြီးစီး
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $enrollment->progress_percentage }}%"></div>
                    </div>
                </div>
                
                <div class="divide-y divide-gray-200">
                    @foreach($course->sections as $section)
                    <div class="p-4">
                        <h3 class="font-medium text-gray-900 mb-3 myanmar-text">{{ $section->title }}</h3>
                        <div class="space-y-2">
                            @foreach($section->lessons as $sectionLesson)
                            <div class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer {{ $lesson->id === $sectionLesson->id ? 'bg-blue-50 border border-blue-200' : '' }}"
                                 onclick="window.location.href='{{ route('courses.lesson', [$course, $sectionLesson->id]) }}'">
                                <div class="flex-shrink-0">
                                    @if($sectionLesson->isCompletedBy(auth()->id()))
                                        <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                            <i class="fas fa-check text-white text-xs"></i>
                                        </div>
                                    @elseif($lesson->id === $sectionLesson->id)
                                        <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center">
                                            <i class="fas fa-play text-white text-xs"></i>
                                        </div>
                                    @else
                                        <div class="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center">
                                            <i class="fas fa-play text-white text-xs"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate myanmar-text">{{ $sectionLesson->title }}</p>
                                    @if($sectionLesson->video_duration)
                                    <p class="text-xs text-gray-500">{{ $sectionLesson->formatted_duration }}</p>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('lesson-video');
    if (video) {
        let lastUpdateTime = 0;
        
        video.addEventListener('timeupdate', function() {
            const currentTime = Math.floor(video.currentTime);
            const duration = Math.floor(video.duration);
            
            // Update progress every 5 seconds
            if (currentTime - lastUpdateTime >= 5) {
                lastUpdateTime = currentTime;
                trackVideoProgress({{ $lesson->id }}, currentTime, duration);
            }
        });
        
        video.addEventListener('ended', function() {
            // Mark lesson as completed when video ends
            fetch(`/lessons/{{ $lesson->id }}/complete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('သင်ခန်းစာ ပြီးစီးပါပြီ!', 'success');
                    // Reload page to update progress
                    setTimeout(() => location.reload(), 1000);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }
});
</script>
@endpush
@endsection