@extends('layouts.app')

@section('title', $lesson->title . ' - ' . $course->title)

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-0">

            {{-- ── Video + Lesson Info ───────────────────────────────────────── --}}
            <div class="lg:col-span-3 bg-black">

                {{-- Video Player --}}
                <div class="aspect-video bg-gray-900 flex items-center justify-center">
                    @if($lesson->video_url)
                        @if($lesson->isYoutubeUrl($lesson->video_url))
                            <iframe id="lesson-video"
                                    class="w-full h-full"
                                    src="{{ $lesson->youtube_embed_url }}"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen>
                            </iframe>
                        @else
                            <video id="lesson-video" class="w-full h-full" controls>
                                <source src="{{ $lesson->video_url_full }}" type="video/mp4">
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

                {{-- Lesson Info + Mark as Done Button --}}
                @php $alreadyDone = $lesson->isCompletedBy(auth()->id()); @endphp
                <div class="bg-white p-6 border-b">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">

                        {{-- Title & breadcrumb --}}
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 mb-2 myanmar-text">{{ $lesson->title }}</h1>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500">
                                <span class="myanmar-text">{{ $course->title }}</span>
                                <span>•</span>
                                <span class="myanmar-text">{{ $lesson->section->title }}</span>
                                @if($lesson->video_duration)
                                    <span>•</span>
                                    <span>{{ $lesson->formatted_duration }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- ── DONE BUTTON ─────────────────────────────── --}}
                        <div style="flex-shrink:0; margin-top:4px;">
                            @if($alreadyDone)
                                <span id="mark-done-btn"
                                      style="display:inline-flex; align-items:center; gap:8px;
                                             padding:10px 22px; border-radius:12px; font-weight:700;
                                             font-size:14px; background:#16a34a; color:#fff;
                                             border:none; box-shadow:0 2px 8px rgba(0,0,0,.15);">
                                    <i class="fas fa-check-circle"></i> Completed!
                                </span>
                            @else
                                <button id="mark-done-btn"
                                        onclick="doMarkDone({{ $lesson->id }})"
                                        style="display:inline-flex; align-items:center; gap:8px;
                                               padding:10px 22px; border-radius:12px; font-weight:700;
                                               font-size:14px; background:#0d9488; color:#fff;
                                               border:none; cursor:pointer;
                                               box-shadow:0 2px 8px rgba(0,0,0,.15);
                                               transition:background .2s;"
                                        onmouseover="this.style.background='#2563eb'"
                                        onmouseout="this.style.background='#0d9488'">
                                    <i class="fas fa-check"></i> Mark as Done
                                </button>
                            @endif
                        </div>

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

            {{-- ── Sidebar ────────────────────────────────────────────────────── --}}
            <div class="lg:col-span-1 bg-white border-l border-gray-200 max-h-screen overflow-y-auto">

                {{-- Progress header --}}
                <div class="p-4 border-b border-gray-200">
                    <h2 class="font-semibold text-gray-900 mb-1 myanmar-text">သင်ခန်းစာအကြောင်းအရာ</h2>
                    <div class="text-sm text-gray-600 myanmar-text mb-2">
                        {{ $enrollment->progress_percentage }}% ပြီးစီး
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-teal-500 h-2 rounded-full transition-all duration-500"
                             style="width: {{ $enrollment->progress_percentage }}%"></div>
                    </div>
                </div>

                {{-- Lesson list --}}
                <div class="divide-y divide-gray-100">
                    @foreach($course->sections as $section)
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-800 mb-3 text-sm uppercase tracking-wide myanmar-text">
                            {{ $section->title }}
                        </h3>
                        <div class="space-y-1">
                            @foreach($section->lessons as $sectionLesson)
                            @php $isDone = $sectionLesson->isCompletedBy(auth()->id()); @endphp
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer
                                        {{ $lesson->id === $sectionLesson->id ? 'bg-teal-50 border border-teal-200' : '' }}"
                                 onclick="window.location.href='{{ route('courses.lesson', [$course, $sectionLesson->id]) }}'">

                                {{-- Status circle --}}
                                <div class="flex-shrink-0">
                                    @if($isDone)
                                        <div class="w-7 h-7 bg-green-500 rounded-full flex items-center justify-center shadow-sm">
                                            <i class="fas fa-check text-white" style="font-size:10px"></i>
                                        </div>
                                    @elseif($lesson->id === $sectionLesson->id)
                                        <div class="w-7 h-7 bg-teal-500 rounded-full flex items-center justify-center shadow-sm">
                                            <i class="fas fa-play text-white" style="font-size:10px"></i>
                                        </div>
                                    @else
                                        <div class="w-7 h-7 bg-gray-300 rounded-full flex items-center justify-center">
                                            <i class="fas fa-play text-white" style="font-size:10px"></i>
                                        </div>
                                    @endif
                                </div>

                                {{-- Lesson title --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate myanmar-text
                                              {{ $lesson->id === $sectionLesson->id ? 'text-teal-700' : '' }}">
                                        {{ $sectionLesson->title }}
                                    </p>
                                    @if($sectionLesson->video_duration)
                                        <p class="text-xs text-gray-400">{{ $sectionLesson->formatted_duration }}</p>
                                    @endif
                                </div>

                                {{-- Done badge / button in sidebar (inline styles for reliability) --}}
                                <div style="flex-shrink:0" onclick="event.stopPropagation()">
                                    @if($isDone)
                                        {{-- Already done: static green badge --}}
                                        <span style="font-size:11px; font-weight:700;
                                                     color:#15803d; background:#f0fdf4;
                                                     border:1.5px solid #16a34a;
                                                     border-radius:6px; padding:3px 9px;
                                                     white-space:nowrap;">
                                            Done ✓
                                        </span>
                                    @else
                                        {{-- Not done: teal button, blue on hover --}}
                                        <button onclick="doMarkDone({{ $sectionLesson->id }})"
                                                style="font-size:11px; font-weight:700;
                                                       color:#fff; background:#0d9488;
                                                       border:1.5px solid #0f766e;
                                                       border-radius:6px; padding:3px 9px;
                                                       cursor:pointer; white-space:nowrap;
                                                       transition:background .15s;"
                                                onmouseover="this.style.background='#2563eb';this.style.borderColor='#1d4ed8';"
                                                onmouseout="this.style.background='#0d9488';this.style.borderColor='#0f766e';">
                                            Done
                                        </button>
                                    @endif
                                </div>

                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>
            {{-- ── End Sidebar ─────────────────────────────────────────────────── --}}

        </div>
    </div>
</div>
@endsection

{{-- Scripts MUST be OUTSIDE @section to avoid Blade parsing conflicts --}}
@push('scripts')
<script>
// ── Mark lesson as done ────────────────────────────────────────────────────────
function doMarkDone(lessonId) {
    var btn = document.getElementById('mark-done-btn');
    if (btn && btn.tagName === 'BUTTON') {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';
    }

    fetch('/lessons/' + lessonId + '/complete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            var pct = data.progress_percentage !== undefined ? data.progress_percentage : '';
            showToast('✅ Saved! Progress: ' + pct + '% ပြီးစီး');
            setTimeout(function() { location.reload(); }, 1300);
        } else {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Mark as Done'; }
            showToast('⚠️ Error. Please try again.');
        }
    })
    .catch(function(err) {
        console.error('Error:', err);
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Mark as Done'; }
        showToast('⚠️ Network error.');
    });
}

// ── Toast notification ─────────────────────────────────────────────────────────
function showToast(message) {
    var toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);' +
        'background:#065f46;color:#fff;padding:12px 24px;border-radius:12px;' +
        'font-weight:600;font-size:14px;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,.25);';
    document.body.appendChild(toast);
    setTimeout(function() { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 3500);
}

// ── Auto-complete when HTML5 video ends ────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    var video = document.getElementById('lesson-video');
    if (video && video.tagName === 'VIDEO') {
        video.addEventListener('ended', function() {
            doMarkDone({{ $lesson->id }});
        });
    }
});
</script>
@endpush

