@extends('layouts.lecturer')

@section('title', 'Course Content')
@section('page-title', 'Manage Course Content')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('instructor.courses') }}">Courses</a></li>
<li class="breadcrumb-item active">Content</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-book mr-2"></i>{{ $course->title }}</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-primary" onclick="openSectionModal()">
                <i class="fas fa-plus"></i> Add Section
            </button>
        </div>
    </div>
    <div class="card-body">
        @forelse($course->sections as $section)
        <div class="card card-outline card-primary mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-folder mr-2"></i>{{ $section->title }}
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-success btn-sm" onclick="openLessonModal({{ $section->id }})">
                        <i class="fas fa-plus"></i> Add Lesson
                    </button>
                    <button type="button" class="btn btn-info btn-sm" onclick="editSection({{ $section->id }}, '{{ addslashes($section->title) }}', '{{ addslashes($section->description ?? '') }}')">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteSection({{ $course->id }}, {{ $section->id }})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                @if($section->description)
                <div class="px-3 pt-3 pb-2 text-muted">
                    <small>{{ $section->description }}</small>
                </div>
                @endif
                
                @forelse($section->lessons as $lesson)
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="mr-3">
                            @if($lesson->type === 'video')
                                <div class="btn btn-sm btn-primary rounded-circle" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-play"></i>
                                </div>
                            @elseif($lesson->type === 'text')
                                <div class="btn btn-sm btn-success rounded-circle" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            @elseif($lesson->type === 'quiz')
                                <div class="btn btn-sm btn-warning rounded-circle" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-question-circle"></i>
                                </div>
                            @else
                                <div class="btn btn-sm btn-secondary rounded-circle" style="width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-file"></i>
                                </div>
                            @endif
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $lesson->title }}</h6>
                            <small class="text-muted">
                                <span class="badge badge-secondary">{{ ucfirst($lesson->type) }}</span>
                                @if($lesson->video_duration)
                                <span class="ml-2"><i class="far fa-clock"></i> {{ $lesson->formatted_duration }}</span>
                                @endif
                                @if($lesson->is_preview)
                                <span class="badge badge-info ml-2">Preview</span>
                                @endif
                            </small>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-info" onclick="editLesson({{ $lesson->id }}, {{ $section->id }}, '{{ addslashes($lesson->title) }}', '{{ addslashes($lesson->description ?? '') }}', '{{ $lesson->type }}', '{{ $lesson->video_url ?? '' }}', {{ $lesson->video_duration ?? 0 }}, {{ $lesson->is_preview ? 'true' : 'false' }}, '{{ addslashes($lesson->content ?? '') }}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteLesson({{ $course->id }}, {{ $section->id }}, {{ $lesson->id }})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                @empty
                <div class="p-5 text-center text-muted">
                    <i class="fas fa-video fa-3x mb-3"></i>
                    <p>No lessons in this section yet</p>
                    <button type="button" class="btn btn-success btn-sm" onclick="openLessonModal({{ $section->id }})">
                        <i class="fas fa-plus"></i> Add First Lesson
                    </button>
                </div>
                @endforelse
            </div>
        </div>
        @empty
        <div class="text-center py-5">
            <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
            <h4>No Sections Yet</h4>
            <p class="text-muted">Create your first section to start adding course content</p>
            <button type="button" class="btn btn-primary" onclick="openSectionModal()">
                <i class="fas fa-plus mr-2"></i>Add First Section
            </button>
        </div>
        @endforelse
    </div>
</div>

<!-- Section Modal -->
<div class="modal fade" id="sectionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Section</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="sectionForm">
                @csrf
                <input type="hidden" id="section_id" name="section_id">
                <input type="hidden" id="section_method" name="_method" value="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="section_title">Section Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="section_title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="section_description">Description</label>
                        <textarea class="form-control" id="section_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Add Section
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lesson Modal -->
<div class="modal fade" id="lessonModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Lesson</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="lessonForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="lesson_section_id" name="section_id">
                <input type="hidden" id="lesson_id" name="lesson_id">
                <input type="hidden" id="lesson_method" name="_method" value="POST">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="lesson_title">Lesson Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lesson_title" name="title" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="lesson_type">Type <span class="text-danger">*</span></label>
                                <select class="form-control" id="lesson_type" name="type" required onchange="toggleVideoFields()">
                                    <option value="video">Video</option>
                                    <option value="text">Text</option>
                                    <option value="quiz">Quiz</option>
                                    <option value="assignment">Assignment</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="lesson_description">Description</label>
                        <textarea class="form-control" id="lesson_description" name="description" rows="2"></textarea>
                    </div>

                    <!-- Video Fields -->
                    <div id="videoFields">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-video mr-2"></i>Video Settings</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="video_url">Video CDN URL <span class="text-danger">*</span></label>
                                    <input type="url" class="form-control" id="video_url" name="video_url"
                                           placeholder="https://cdn.example.com/video.mp4">
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Enter YouTube, Vimeo, CDN link or direct video URL
                                    </small>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="video_duration">Duration (seconds)</label>
                                            <input type="number" class="form-control" id="video_duration" name="video_duration" min="0" placeholder="e.g., 300">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="is_preview" name="is_preview" value="1">
                                                <label class="custom-control-label" for="is_preview">
                                                    <i class="fas fa-eye mr-1"></i>Preview Lesson (Free to watch)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Text Content -->
                    <div id="textFields" class="d-none">
                        <div class="form-group">
                            <label for="lesson_content">Content</label>
                            <textarea class="form-control" id="lesson_content" name="content" rows="6"></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Add Lesson
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Show session messages with SweetAlert2
@if(session('success'))
    Swal.fire({
        title: 'Success!',
        text: '{{ session('success') }}',
        icon: 'success',
        timer: 3000,
        showConfirmButton: false
    });
@endif

@if(session('error'))
    Swal.fire({
        title: 'Error!',
        text: '{{ session('error') }}',
        icon: 'error',
        confirmButtonText: 'OK'
    });
@endif

// Section Modal Functions
function openSectionModal() {
    document.getElementById('section_id').value = '';
    document.getElementById('section_method').value = 'POST';
    document.getElementById('sectionForm').reset();
    document.querySelector('#sectionModal .modal-title').textContent = 'Add New Section';
    $('#sectionModal').modal('show');
}

function editSection(sectionId, title, description) {
    document.getElementById('section_id').value = sectionId;
    document.getElementById('section_method').value = 'PUT';
    document.getElementById('section_title').value = title;
    document.getElementById('section_description').value = description;
    document.querySelector('#sectionModal .modal-title').textContent = 'Edit Section';
    $('#sectionModal').modal('show');
}

function deleteSection(courseId, sectionId) {
    Swal.fire({
        title: 'Delete Section?',
        text: 'All lessons in this section will also be deleted. This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/instructor/courses/${courseId}/sections/${sectionId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        try {
                            const data = JSON.parse(text);
                            throw new Error(data.message || 'Error deleting section');
                        } catch (e) {
                            if (text.includes('<!DOCTYPE') || text.includes('<html')) {
                                throw new Error('Server error occurred. Please check the logs.');
                            }
                            throw new Error('Error deleting section');
                        }
                    });
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid response from server');
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: data.message || 'Section has been deleted successfully.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = window.location.href.split('?')[0];
                    });
                } else {
                    Swal.fire('Error!', data.message || 'Error deleting section', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', error.message || 'An unexpected error occurred while deleting the section', 'error');
            });
        }
    });
}

function closeSectionModal() {
    $('#sectionModal').modal('hide');
    document.getElementById('sectionForm').reset();
}

// Lesson Modal Functions
function openLessonModal(sectionId) {
    document.getElementById('lesson_section_id').value = sectionId;
    document.getElementById('lesson_id').value = '';
    document.getElementById('lesson_method').value = 'POST';
    document.getElementById('lessonForm').reset();
    document.querySelector('#lessonModal .modal-title').textContent = 'Add New Lesson';
    toggleVideoFields();
    $('#lessonModal').modal('show');
}

function editLesson(lessonId, sectionId, title, description, type, videoUrl, videoDuration, isPreview, content) {
    document.getElementById('lesson_id').value = lessonId;
    document.getElementById('lesson_section_id').value = sectionId;
    document.getElementById('lesson_method').value = 'PUT';
    document.getElementById('lesson_title').value = title;
    document.getElementById('lesson_description').value = description;
    document.getElementById('lesson_type').value = type;
    document.getElementById('video_url').value = videoUrl;
    document.getElementById('video_duration').value = videoDuration;
    document.getElementById('is_preview').checked = isPreview;
    document.getElementById('lesson_content').value = content;
    document.querySelector('#lessonModal .modal-title').textContent = 'Edit Lesson';
    toggleVideoFields();
    $('#lessonModal').modal('show');
}

function deleteLesson(courseId, sectionId, lessonId) {
    Swal.fire({
        title: 'Delete Lesson?',
        text: 'This action cannot be undone!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/instructor/courses/${courseId}/sections/${sectionId}/lessons/${lessonId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        try {
                            const data = JSON.parse(text);
                            throw new Error(data.message || 'Error deleting lesson');
                        } catch (e) {
                            if (text.includes('<!DOCTYPE') || text.includes('<html')) {
                                throw new Error('Server error occurred. Please check the logs.');
                            }
                            throw new Error('Error deleting lesson');
                        }
                    });
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error('Invalid response from server');
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: data.message || 'Lesson has been deleted successfully.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = window.location.href.split('?')[0];
                    });
                } else {
                    Swal.fire('Error!', data.message || 'Error deleting lesson', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', error.message || 'An unexpected error occurred while deleting the lesson', 'error');
            });
        }
    });
}

function closeLessonModal() {
    $('#lessonModal').modal('hide');
    document.getElementById('lessonForm').reset();
}

function toggleVideoFields() {
    const type = document.getElementById('lesson_type').value;
    const videoFields = document.getElementById('videoFields');
    const textFields = document.getElementById('textFields');
    
    if (type === 'video') {
        videoFields.classList.remove('d-none');
        textFields.classList.add('d-none');
    } else if (type === 'text') {
        videoFields.classList.add('d-none');
        textFields.classList.remove('d-none');
    } else {
        videoFields.classList.add('d-none');
        textFields.classList.add('d-none');
    }
}

// Form Submissions
document.getElementById('sectionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const sectionId = document.getElementById('section_id').value;
    const method = document.getElementById('section_method').value;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Saving...';
    
    let url = `{{ route('instructor.courses.sections.store', $course) }}`;
    if (sectionId && method === 'PUT') {
        url = `/instructor/courses/{{ $course->id }}/sections/${sectionId}`;
        formData.append('_method', 'PUT');
    }
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                try {
                    const data = JSON.parse(text);
                    throw new Error(data.message || 'Error saving section');
                } catch (e) {
                    // If response is HTML (Laravel error page), show generic error
                    if (text.includes('<!DOCTYPE') || text.includes('<html')) {
                        throw new Error('Server error occurred. Please check the logs.');
                    }
                    throw new Error('Error saving section');
                }
            });
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('Invalid response from server');
            }
        });
    })
    .then(data => {
        if (data.success) {
            closeSectionModal();
            Swal.fire({
                title: 'Success!',
                text: sectionId ? 'Section updated successfully!' : 'Section created successfully!',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = window.location.href.split('?')[0];
            });
        } else {
            Swal.fire('Error!', data.message || 'Error saving section', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-1"></i>' + (sectionId ? 'Update Section' : 'Add Section');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error!', error.message || 'Error saving section', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-1"></i>' + (sectionId ? 'Update Section' : 'Add Section');
    });
});

document.getElementById('lessonForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const sectionId = document.getElementById('lesson_section_id').value;
    const lessonId = document.getElementById('lesson_id').value;
    const method = document.getElementById('lesson_method').value;
    const submitBtn = this.querySelector('button[type="submit"]');
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Saving...';
    
    let url = `/instructor/courses/{{ $course->id }}/sections/${sectionId}/lessons`;
    if (lessonId && method === 'PUT') {
        url = `/instructor/courses/{{ $course->id }}/sections/${sectionId}/lessons/${lessonId}`;
        formData.append('_method', 'PUT');
    }
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                try {
                    const data = JSON.parse(text);
                    throw new Error(data.message || 'Error saving lesson');
                } catch (e) {
                    // If response is HTML (Laravel error page), show generic error
                    if (text.includes('<!DOCTYPE') || text.includes('<html')) {
                        throw new Error('Server error occurred. Please check the logs.');
                    }
                    throw new Error('Error saving lesson');
                }
            });
        }
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('Invalid response from server');
            }
        });
    })
    .then(data => {
        if (data.success) {
            closeLessonModal();
            Swal.fire({
                title: 'Success!',
                text: lessonId ? 'Lesson updated successfully!' : 'Lesson created successfully!',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                window.location.href = window.location.href.split('?')[0];
            });
        } else {
            Swal.fire('Error!', data.message || 'Error saving lesson', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-1"></i>' + (lessonId ? 'Update Lesson' : 'Add Lesson');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error!', error.message || 'Error saving lesson', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-1"></i>' + (lessonId ? 'Update Lesson' : 'Add Lesson');
    });
});
</script>
@endpush
@endsection