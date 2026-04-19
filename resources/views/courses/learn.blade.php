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
                @php $alreadyDone = $currentLesson->isCompletedBy(auth()->id()); @endphp
                <div class="bg-white p-6 border-b">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
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

                        {{-- Mark as Done Button --}}
                        <div class="flex-shrink-0">
                            @if($alreadyDone)
                                <span id="done-badge"
                                      class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm
                                             bg-green-100 text-green-700 border border-green-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Completed!
                                </span>
                            @else
                                <button id="mark-done-btn"
                                        onclick="markLessonDone()"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm
                                               bg-teal-600 hover:bg-teal-700 text-white shadow-md hover:shadow-lg
                                               transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0
                                               disabled:opacity-60 disabled:cursor-not-allowed">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Mark as Done
                                </button>
                            @endif
                        </div>
                    </div>
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
                            <div class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer {{ $currentLesson && $currentLesson->id === $lesson->id ? 'bg-blue-50 border border-blue-200' : '' }}"
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
// ─── Mark as Done button click ────────────────────────────────────────────────
function markLessonDone() {
    @if(!$currentLesson) return; @endif

    const lessonId = {{ $currentLesson->id }};
    const btn = document.getElementById('mark-done-btn');

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
            // Swap button → green badge instantly
            if (btn) {
                btn.outerHTML = `
                    <span id="done-badge"
                          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm
                                 bg-green-100 text-green-700 border border-green-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Completed!
                    </span>`;
            }
            showToast('✅ သင်ခန်းစာ ပြီးစီးပါပြီ! Lesson marked as done.');
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
            markLessonDone();
        });
    }
});

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