@extends('layouts.app')

@section('title', $lesson->title . ' - ' . $course->title)

@section('content')

{{-- ── Button CSS (uses !important to override Tailwind preflight reset) ── --}}
<style>
/* Main "Mark as Done" button */
.done-btn-main {
    display: inline-flex !important;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 14px;
    background-color: #0d9488 !important;
    color: #ffffff !important;
    border: 2px solid #0f766e !important;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    transition: background-color 0.2s, border-color 0.2s;
}
.done-btn-main:hover {
    background-color: #2563eb !important;
    border-color: #1d4ed8 !important;
}

/* Completed badge (non-clickable) */
.done-badge-main {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 24px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 14px;
    background-color: #16a34a;
    color: #ffffff;
    border: 2px solid #15803d;
}

/* Sidebar "Done" button (small) */
.done-btn-sidebar {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #ffffff !important;
    background-color: #0d9488 !important;
    border: 1.5px solid #0f766e !important;
    border-radius: 6px !important;
    padding: 3px 10px !important;
    cursor: pointer !important;
    white-space: nowrap !important;
    transition: background-color 0.15s, border-color 0.15s !important;
}
.done-btn-sidebar:hover {
    background-color: #2563eb !important;
    border-color: #1d4ed8 !important;
    color: #ffffff !important;
}

/* Sidebar "Done ✓" badge (non-clickable) */
.done-badge-sidebar {
    display: inline-block;
    font-size: 11px;
    font-weight: 700;
    color: #15803d;
    background-color: #f0fdf4;
    border: 1.5px solid #16a34a;
    border-radius: 6px;
    padding: 3px 10px;
    white-space: nowrap;
}
</style>

<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-0">

            {{-- ── Video + Lesson Info ────────────────────────────────────────── --}}
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
                            </video>
                        @endif
                    @else
                        <div class="text-center text-white">
                            <i class="fas fa-play-circle text-6xl mb-4 opacity-50"></i>
                            <p class="text-xl myanmar-text">ဗီဒီယို မရရှိနိုင်ပါ</p>
                        </div>
                    @endif
                </div>

                {{-- Lesson Info + Done Button --}}
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

                        {{-- Done / Completed Button --}}
                        <div style="flex-shrink:0; padding-top:4px">
                            @if($alreadyDone)
                                <span class="done-badge-main">
                                    <i class="fas fa-check-circle"></i> Completed!
                                </span>
                            @else
                                <button id="main-done-btn"
                                        class="done-btn-main"
                                        onclick="doMarkDone({{ $lesson->id }})">
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
            {{-- ── End Video+Info ──────────────────────────────────────────────── --}}

            {{-- ── Sidebar ─────────────────────────────────────────────────────── --}}
            <div class="lg:col-span-1 bg-white border-l border-gray-200 max-h-screen overflow-y-auto">

                {{-- Progress header --}}
                <div class="p-4 border-b border-gray-200">
                    <h2 class="font-semibold text-gray-900 mb-1 myanmar-text">သင်ခန်းစာအကြောင်းအရာ</h2>
                    <div class="text-sm text-gray-600 myanmar-text mb-2">
                        {{ $enrollment->progress_percentage }}% ပြီးစီး
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-teal-500 h-2 rounded-full"
                             style="width: {{ $enrollment->progress_percentage }}%"></div>
                    </div>
                </div>

                {{-- Lesson list --}}
                <div class="divide-y divide-gray-100">
                    @foreach($course->sections as $section)
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-700 mb-3 text-xs uppercase tracking-wider myanmar-text">
                            {{ $section->title }}
                        </h3>
                        <div class="space-y-1">
                            @foreach($section->lessons as $sectionLesson)
                            @php $isDone = $sectionLesson->isCompletedBy(auth()->id()); @endphp
                            <div class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-50 cursor-pointer
                                        {{ $lesson->id === $sectionLesson->id ? 'bg-teal-50 border border-teal-200' : '' }}"
                                 onclick="window.location.href='{{ route('courses.lesson', [$course, $sectionLesson->id]) }}'">

                                {{-- Status circle --}}
                                <div style="flex-shrink:0">
                                    @if($isDone)
                                        <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                            <i class="fas fa-check text-white" style="font-size:9px"></i>
                                        </div>
                                    @elseif($lesson->id === $sectionLesson->id)
                                        <div class="w-6 h-6 bg-teal-500 rounded-full flex items-center justify-center">
                                            <i class="fas fa-play text-white" style="font-size:9px"></i>
                                        </div>
                                    @else
                                        <div class="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center">
                                            <i class="fas fa-play text-white" style="font-size:9px"></i>
                                        </div>
                                    @endif
                                </div>

                                {{-- Lesson title --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-gray-900 truncate myanmar-text">
                                        {{ $sectionLesson->title }}
                                    </p>
                                    @if($sectionLesson->video_duration)
                                        <p class="text-xs text-gray-400">{{ $sectionLesson->formatted_duration }}</p>
                                    @endif
                                </div>

                                {{-- Done button / badge --}}
                                <div style="flex-shrink:0" onclick="event.stopPropagation()">
                                    @if($isDone)
                                        <span class="done-badge-sidebar">Done ✓</span>
                                    @else
                                        <button class="done-btn-sidebar"
                                                onclick="doMarkDone({{ $sectionLesson->id }})">
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
            {{-- ── End Sidebar ──────────────────────────────────────────────────── --}}

        </div>
    </div>
</div>
@endsection

{{-- ── Scripts (OUTSIDE @section — required to avoid Blade directive conflicts) ── --}}
@push('scripts')
<script>
function doMarkDone(lessonId) {
    // Disable whichever button was clicked
    var btn = document.getElementById('main-done-btn');

    if (btn) {
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
            var pct = (data.progress_percentage !== undefined) ? data.progress_percentage : '?';
            showToast('✅ Saved! Progress: ' + pct + '% ပြီးစီး');
            setTimeout(function() { location.reload(); }, 1400);
        } else {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Mark as Done'; }
            showToast('⚠️ Error. Please try again.');
        }
    })
    .catch(function(err) {
        console.error('doMarkDone error:', err);
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-check"></i> Mark as Done'; }
        showToast('⚠️ Network error. Please try again.');
    });
}

function showToast(message) {
    var t = document.createElement('div');
    t.textContent = message;
    t.style.cssText = [
        'position:fixed', 'bottom:24px', 'left:50%', 'transform:translateX(-50%)',
        'background:#065f46', 'color:#fff', 'padding:12px 24px', 'border-radius:12px',
        'font-weight:700', 'font-size:14px', 'z-index:99999',
        'box-shadow:0 8px 24px rgba(0,0,0,.3)'
    ].join(';');
    document.body.appendChild(t);
    setTimeout(function() { if (t.parentNode) t.parentNode.removeChild(t); }, 3500);
}

// Auto-mark complete when HTML5 video ends
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
