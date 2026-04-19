@extends('layouts.app')

@section('title', $course->title . ' - သင်ယူရန်')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-0">
            <!-- Video Player Area -->
            <div class="lg:col-span-3 bg-black">
                <div class="aspect-video bg-gray-900 flex items-center justify-center">
                    @if($currentLesson && $currentLesson->video_url)
                        @if($currentLesson->isYoutubeUrl($currentLesson->video_url))
                            <!-- YouTube Video -->
                            <iframe id="lesson-video" 
                                    class="w-full h-full" 
                                    src="{{ $currentLesson->youtube_embed_url }}" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen>
                            </iframe>
                        @else
                            <!-- Local/Direct Video -->
                            <video id="lesson-video" class="w-full h-full" controls>
                                <source src="{{ $currentLesson->video_url_full }}" type="video/mp4">
                                သင့်ဘရောက်ဆာသည် ဗီဒီယိုကို ပံ့ပိုးမပေးပါ။
                            </video>
                        @endif
                    @else
                        <div class="text-center text-white">
                            <i class="fas fa-play-circle text-6xl mb-4 opacity-50"></i>
                            <p class="text-xl myanmar-text">ဗီဒီယို မရရှိနိုင်ပါ</p>
                        </div>
                    @endif
                </div>
                
                <!-- Lesson Info -->
                @if($currentLesson)
                <div class="bg-white p-6 border-b">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2 myanmar-text">{{ $currentLesson->title }}</h1>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-600">
                        <span class="myanmar-text">{{ $course->title }}</span>
                        <span>•</span>
                        <span class="myanmar-text">{{ $currentLesson->section->title }}</span>
                        @if($currentLesson->video_duration)
                        <span>•</span>
                        <span>{{ $currentLesson->formatted_duration }}</span>
                        @endif
                    </div>
                    @if($currentLesson->description)
                    <p class="mt-4 text-gray-700 myanmar-text">{{ $currentLesson->description }}</p>
                    @endif
                </div>
                @endif
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
                            @foreach($section->lessons as $lesson)
                            @php $lessonDone = $lesson->progress && $lesson->progress->first() && $lesson->progress->first()->is_completed; @endphp
                            <div class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 {{ $currentLesson && $currentLesson->id === $lesson->id ? 'bg-blue-50 border border-blue-200' : '' }}"
                                 style="cursor:pointer"
                                 onclick="window.location.href='{{ route('courses.lesson', [$course->slug, $lesson->id]) }}'">
                                <div class="flex-shrink-0">
                                    @if($lesson->progress && $lesson->progress->first() && $lesson->progress->first()->is_completed)
                                        <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                            <i class="fas fa-check text-white text-xs"></i>
                                        </div>
                                    @elseif($currentLesson && $currentLesson->id === $lesson->id)
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
                                    <p class="text-sm font-medium text-gray-900 truncate myanmar-text">{{ $lesson->title }}</p>
                                    @if($lesson->video_duration)
                                    <p class="text-xs text-gray-500">{{ $lesson->formatted_duration }}</p>
                                    @endif
                                </div>
                                {{-- Completed button in sidebar --}}
                                <div class="flex-shrink-0" onclick="event.stopPropagation()">
                                    @if($lessonDone)
                                        <button
                                            onclick="markLessonIncomplete({{ $lesson->id }}, this)"
                                            style="transition: all 0.15s;"
                                            onmouseover="this.style.backgroundColor='#3B82F6 !important'; this.style.color='white !important'; this.style.borderColor='#3B82F6 !important';"
                                            onmouseout="this.style.backgroundColor=''; this.style.color=''; this.style.borderColor='';"
                                            class="text-xs text-green-600 font-semibold bg-green-50 border border-green-200 rounded-md px-2 py-1
                                                   disabled:opacity-50 disabled:cursor-not-allowed">
                                            Completed ✓
                                        </button>
                                    @else
                                        <button
                                            onclick="markLessonDone({{ $lesson->id }}, this)"
                                            style="transition: all 0.15s;"
                                            onmouseover="this.style.backgroundColor='#14B8A6'; this.style.color='white'; this.style.borderColor='#14B8A6';"
                                            onmouseout="this.style.backgroundColor=''; this.style.color=''; this.style.borderColor='';"
                                            class="text-xs text-teal-700 font-semibold bg-white border border-teal-400 rounded-md px-2 py-1 disabled:opacity-50 disabled:cursor-not-allowed">
                                            Mark Complete
                                        </button>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                    
                    {{-- Exams Section --}}
                    @if($course->exams->where('is_published', true)->count() > 0)
                    <div class="p-4 bg-yellow-50 border-t-2 border-yellow-400">
                        <h3 class="font-medium text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-clipboard-check text-yellow-600 mr-2"></i>
                            <span class="myanmar-text">စာမေးပွဲများ</span>
                        </h3>
                        <div class="space-y-2">
                            @foreach($course->exams->where('is_published', true) as $exam)
                            <div class="flex items-center space-x-3 p-2 rounded-lg hover:bg-yellow-100 cursor-pointer border border-yellow-200"
                                 onclick="window.location.href='{{ route('exams.start', $exam) }}'">
                                <div class="flex-shrink-0">
                                    <div class="w-6 h-6 bg-yellow-500 rounded-full flex items-center justify-center">
                                        <i class="fas fa-file-alt text-white text-xs"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate myanmar-text">{{ $exam->title }}</p>
                                    <p class="text-xs text-gray-500 myanmar-text">
                                        @if($exam->duration_minutes)
                                            {{ $exam->duration_minutes }} မိနစ်
                                        @else
                                            အချိန်ကန့်သတ်မရှိ
                                        @endif
                                        • {{ $exam->questions->count() }} မေးခွန်း
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    @php
                                        $userAttempts = $exam->userAttempts(auth()->id());
                                        $canAttempt = $exam->canUserAttempt(auth()->id());
                                    @endphp
                                    @if($userAttempts > 0)
                                        <span class="text-xs text-gray-600 bg-gray-100 border border-gray-300 rounded-md px-2 py-1 myanmar-text">
                                            {{ $userAttempts }}/{{ $exam->max_attempts }} ကြိမ်
                                        </span>
                                    @else
                                        <span class="text-xs text-yellow-700 font-semibold bg-yellow-100 border border-yellow-300 rounded-md px-2 py-1 myanmar-text">
                                            စတင်မည်
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ─── Mark as Done button click (call with lessonId + button element) ─────────
function markLessonDone(lessonId, btn) {

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            Saving…`;
    }

    fetch(`/lessons/${lessonId}/complete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Swap button → clickable green badge
            if (btn) {
                btn.outerHTML = `<button
                    onclick="markLessonIncomplete(${lessonId}, this)"
                    style="transition: all 0.15s;"
                    onmouseover="this.style.backgroundColor='#3B82F6'; this.style.color='white'; this.style.borderColor='#3B82F6';"
                    onmouseout="this.style.backgroundColor=''; this.style.color=''; this.style.borderColor='';"
                    class="text-xs text-green-600 font-semibold bg-green-50 border border-green-200 rounded-md px-2 py-1">
                    Completed ✓
                </button>`;
            }
            showToast('✅ သင်ခန်းစာ ပြီးစီးပါပြီ! Lesson marked as completed.');
            // Reload after short delay — updates sidebar ✅ and progress %
            setTimeout(() => location.reload(), 1400);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        if (btn) { btn.disabled = false; }
        showToast('⚠️ Something went wrong. Please try again.');
    });
}

// ─── HTML5 video: auto-complete when video finishes ──────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    const video = document.getElementById('lesson-video');
    if (video && video.tagName === 'VIDEO') {
        video.addEventListener('ended', function () {
            markLessonDone({{ $currentLesson ? $currentLesson->id : 0 }}, null);
        });
    }
});

// ─── Mark as Incomplete button click ──────────────────────────────────────────
function markLessonIncomplete(lessonId, btn) {

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            Updating…`;
    }

    fetch(`/lessons/${lessonId}/incomplete`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Swap button back to "Mark Complete"
            if (btn) {
                btn.outerHTML = `<button
                    onclick="markLessonDone(${lessonId}, this)"
                    style="transition: all 0.15s;"
                    onmouseover="this.style.backgroundColor='#14B8A6'; this.style.color='white'; this.style.borderColor='#14B8A6';"
                    onmouseout="this.style.backgroundColor=''; this.style.color=''; this.style.borderColor='';"
                    class="text-xs text-teal-700 font-semibold bg-white border border-teal-400 rounded-md px-2 py-1">
                    Mark Complete
                </button>`;
            }
            showToast('🔄 Lesson marked as incomplete.');
            // Reload after short delay
            setTimeout(() => location.reload(), 1400);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        if (btn) { btn.disabled = false; }
        showToast('⚠️ Something went wrong. Please try again.');
    });
}

// ─── Toast notification ───────────────────────────────────────────────────────
function showToast(message) {
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(0);
        background: #065f46; color: #fff; padding: 12px 24px; border-radius: 12px;
        font-weight: 600; font-size: 14px; z-index: 9999;
        box-shadow: 0 8px 24px rgba(0,0,0,0.25);
        animation: slideUp 0.3s ease;`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}
</script>
<style>
@keyframes slideUp {
    from { opacity: 0; transform: translateX(-50%) translateY(16px); }
    to   { opacity: 1; transform: translateX(-50%) translateY(0); }
}
</style>
@endpush
@endsection