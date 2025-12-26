@extends('layouts.app')

@section('title', $course->title . ' - အကြောင်းအရာများ')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 myanmar-text">{{ $course->title }}</h1>
                <p class="text-gray-600 mt-2 myanmar-text">သင်ခန်းစာအကြောင်းအရာများကို စီမံခန့်ခွဲပါ</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('courses.show', $course) }}" target="_blank" class="bg-gray-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-gray-700 transition-colors myanmar-text">
                    <i class="fas fa-eye mr-2"></i>ကြည့်ရှုရန်
                </a>
                <button onclick="openSectionModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors myanmar-text">
                    <i class="fas fa-plus mr-2"></i>အပိုင်းအသစ်ထည့်ရန်
                </button>
            </div>
        </div>
    </div>

    <!-- Course Sections -->
    <div class="space-y-6">
        @forelse($course->sections as $section)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 myanmar-text">{{ $section->title }}</h3>
                        @if($section->description)
                        <p class="text-gray-600 mt-1 myanmar-text">{{ $section->description }}</p>
                        @endif
                    </div>
                    <div class="flex space-x-2">
                        <button onclick="openLessonModal({{ $section->id }})" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 myanmar-text">
                            <i class="fas fa-plus mr-1"></i>သင်ခန်းစာထည့်ရန်
                        </button>
                        <button class="bg-gray-600 text-white px-3 py-1 rounded text-sm hover:bg-gray-700">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Lessons -->
            <div class="divide-y divide-gray-200">
                @forelse($section->lessons as $lesson)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                @if($lesson->type === 'video')
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-play text-blue-600"></i>
                                    </div>
                                @elseif($lesson->type === 'text')
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-file-text text-green-600"></i>
                                    </div>
                                @elseif($lesson->type === 'quiz')
                                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-question-circle text-yellow-600"></i>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 myanmar-text">{{ $lesson->title }}</h4>
                                <div class="flex items-center space-x-4 text-sm text-gray-600">
                                    <span class="myanmar-text">{{ ucfirst($lesson->type) }}</span>
                                    @if($lesson->video_duration)
                                    <span>{{ $lesson->formatted_duration }}</span>
                                    @endif
                                    @if($lesson->is_preview)
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs myanmar-text">နမူနာ</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-video text-3xl mb-2"></i>
                    <p class="myanmar-text">ဤအပိုင်းတွင် သင်ခန်းစာများ မရှိသေးပါ</p>
                </div>
                @endforelse
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <i class="fas fa-folder-open text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-900 mb-2 myanmar-text">အပိုင်းများ မရှိသေးပါ</h3>
            <p class="text-gray-600 mb-4 myanmar-text">သင်ခန်းစာအကြောင်းအရာများကို စတင်ထည့်ရန် ပထမအပိုင်းကို ဖန်တီးပါ</p>
            <button onclick="openSectionModal()" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors myanmar-text">
                <i class="fas fa-plus mr-2"></i>ပထမအပိုင်းထည့်ရန်
            </button>
        </div>
        @endforelse
    </div>
</div>

<!-- Section Modal -->
<div id="sectionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 myanmar-text">အပိုင်းအသစ်ထည့်ရန်</h3>
                <form id="sectionForm">
                    @csrf
                    <div class="mb-4">
                        <label for="section_title" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">အပိုင်းခေါင်းစဉ်</label>
                        <input type="text" id="section_title" name="title" required
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label for="section_description" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">ဖော်ပြချက်</label>
                        <textarea id="section_description" name="description" rows="3"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeSectionModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 myanmar-text">
                            မလုပ်တော့ပါ
                        </button>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 myanmar-text">
                            ထည့်ရန်
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Lesson Modal -->
<div id="lessonModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 myanmar-text">သင်ခန်းစာအသစ်ထည့်ရန်</h3>
                <form id="lessonForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="lesson_section_id" name="section_id">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="lesson_title" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">သင်ခန်းစာခေါင်းစဉ်</label>
                            <input type="text" id="lesson_title" name="title" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="lesson_type" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">အမျိုးအစား</label>
                            <select id="lesson_type" name="type" required onchange="toggleVideoFields()"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="video" class="myanmar-text">ဗီဒီယို</option>
                                <option value="text" class="myanmar-text">စာသား</option>
                                <option value="quiz" class="myanmar-text">မေးခွန်း</option>
                                <option value="assignment" class="myanmar-text">အလုပ်ရုံ</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="lesson_description" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">ဖော်ပြချက်</label>
                        <textarea id="lesson_description" name="description" rows="3"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <!-- Video Fields -->
                    <div id="videoFields" class="space-y-4 mb-4">
                        <div class="border-t pt-4">
                            <h4 class="font-medium text-gray-900 mb-3 myanmar-text">ဗီဒီယိုအပ်လုဒ်</h4>
                            
                            <!-- Video Upload Options -->
                            <div class="mb-4">
                                <div class="flex items-center space-x-4 mb-3">
                                    <label class="flex items-center">
                                        <input type="radio" name="video_option" value="url" checked onchange="toggleVideoOption()">
                                        <span class="ml-2 myanmar-text">CDN/YouTube လင့်ခ်</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="video_option" value="file" onchange="toggleVideoOption()">
                                        <span class="ml-2 myanmar-text">ဖိုင်အပ်လုဒ်</span>
                                    </label>
                                </div>

                                <!-- URL Input -->
                                <div id="urlInput">
                                    <label for="video_url" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">ဗီဒီယို URL</label>
                                    <input type="url" id="video_url" name="video_url"
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="https://example.com/video.mp4">
                                    <p class="mt-1 text-sm text-gray-500 myanmar-text">YouTube, Vimeo, CDN လင့်ခ် သို့မဟုတ် တိုက်ရိုက်ဗီဒီယိုလင့်ခ်</p>
                                </div>

                                <!-- File Input -->
                                <div id="fileInput" class="hidden">
                                    <label for="video_file" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">ဗီဒီယိုဖိုင်</label>
                                    <input type="file" id="video_file" name="video_file" accept="video/*"
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                    <p class="mt-1 text-sm text-gray-500 myanmar-text">MP4, AVI, MOV, WMV (အများဆုံး 100MB)</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="video_duration" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">ကြာချိန် (စက္ကန့်)</label>
                                    <input type="number" id="video_duration" name="video_duration" min="0"
                                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="flex items-center">
                                    <label class="flex items-center">
                                        <input type="checkbox" id="is_preview" name="is_preview" value="1">
                                        <span class="ml-2 myanmar-text">နမူနာသင်ခန်းစာ</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Text Content -->
                    <div id="textFields" class="mb-4 hidden">
                        <label for="lesson_content" class="block text-sm font-medium text-gray-700 mb-2 myanmar-text">အကြောင်းအရာ</label>
                        <textarea id="lesson_content" name="content" rows="6"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeLessonModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400 myanmar-text">
                            မလုပ်တော့ပါ
                        </button>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 myanmar-text">
                            ထည့်ရန်
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Section Modal Functions
function openSectionModal() {
    document.getElementById('sectionModal').classList.remove('hidden');
}

function closeSectionModal() {
    document.getElementById('sectionModal').classList.add('hidden');
    document.getElementById('sectionForm').reset();
}

// Lesson Modal Functions
function openLessonModal(sectionId) {
    document.getElementById('lesson_section_id').value = sectionId;
    document.getElementById('lessonModal').classList.remove('hidden');
}

function closeLessonModal() {
    document.getElementById('lessonModal').classList.add('hidden');
    document.getElementById('lessonForm').reset();
}

function toggleVideoFields() {
    const type = document.getElementById('lesson_type').value;
    const videoFields = document.getElementById('videoFields');
    const textFields = document.getElementById('textFields');
    
    if (type === 'video') {
        videoFields.classList.remove('hidden');
        textFields.classList.add('hidden');
    } else if (type === 'text') {
        videoFields.classList.add('hidden');
        textFields.classList.remove('hidden');
    } else {
        videoFields.classList.add('hidden');
        textFields.classList.add('hidden');
    }
}

function toggleVideoOption() {
    const option = document.querySelector('input[name="video_option"]:checked').value;
    const urlInput = document.getElementById('urlInput');
    const fileInput = document.getElementById('fileInput');
    
    if (option === 'url') {
        urlInput.classList.remove('hidden');
        fileInput.classList.add('hidden');
        document.getElementById('video_file').value = '';
    } else {
        urlInput.classList.add('hidden');
        fileInput.classList.remove('hidden');
        document.getElementById('video_url').value = '';
    }
}

// Form Submissions
document.getElementById('sectionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(`{{ route('instructor.courses.sections.store', $course) }}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeSectionModal();
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error creating section', 'error');
    });
});

document.getElementById('lessonForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const sectionId = document.getElementById('lesson_section_id').value;
    
    fetch(`{{ route('instructor.courses.sections.lessons.store', [$course, '__SECTION__']) }}`.replace('__SECTION__', sectionId), {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeLessonModal();
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error creating lesson', 'error');
    });
});
</script>
@endpush
@endsection