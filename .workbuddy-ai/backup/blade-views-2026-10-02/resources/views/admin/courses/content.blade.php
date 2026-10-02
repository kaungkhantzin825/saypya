@extends('layouts.admin')

@section('title', 'Course Content - ' . $course->title)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Course Content: {{ $course->title }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.courses.index') }}">Courses</a></li>
                    <li class="breadcrumb-item active">Content</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <!-- Sections List -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Sections & Lessons</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addSectionModal">
                                <i class="fas fa-plus"></i> Add Section
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @forelse($course->sections as $section)
                        <div class="card card-outline card-primary m-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-folder mr-2"></i>{{ $section->title }}
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-info btn-xs" data-toggle="modal" data-target="#addLessonModal{{ $section->id }}">
                                        <i class="fas fa-plus"></i> Add Lesson
                                    </button>
                                    <button type="button" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#editSectionModal{{ $section->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('admin.courses.sections.destroy', [$course, $section]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this section and all its lessons?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    @forelse($section->lessons as $lesson)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            @if($lesson->type == 'video')
                                                <i class="fas fa-play-circle text-primary mr-2"></i>
                                            @elseif($lesson->type == 'text')
                                                <i class="fas fa-file-alt text-info mr-2"></i>
                                            @elseif($lesson->type == 'quiz')
                                                <i class="fas fa-question-circle text-warning mr-2"></i>
                                            @else
                                                <i class="fas fa-tasks text-success mr-2"></i>
                                            @endif
                                            {{ $lesson->title }}
                                            @if($lesson->is_preview)
                                                <span class="badge badge-success ml-2">Preview</span>
                                            @endif
                                            @if($lesson->video_duration)
                                                <small class="text-muted ml-2">{{ floor($lesson->video_duration / 60) }}:{{ str_pad($lesson->video_duration % 60, 2, '0', STR_PAD_LEFT) }}</small>
                                            @endif
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-warning btn-xs" data-toggle="modal" data-target="#editLessonModal{{ $lesson->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('admin.courses.lessons.destroy', [$course, $section, $lesson]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this lesson?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </li>

                                    <!-- Edit Lesson Modal -->
                                    <div class="modal fade" id="editLessonModal{{ $lesson->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.courses.lessons.update', [$course, $section, $lesson]) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Lesson</h5>
                                                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label>Title</label>
                                                            <input type="text" class="form-control" name="title" value="{{ $lesson->title }}" required>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Type</label>
                                                            <select class="form-control" name="type" required>
                                                                <option value="video" {{ $lesson->type == 'video' ? 'selected' : '' }}>Video</option>
                                                                <option value="text" {{ $lesson->type == 'text' ? 'selected' : '' }}>Text</option>
                                                                <option value="quiz" {{ $lesson->type == 'quiz' ? 'selected' : '' }}>Quiz</option>
                                                                <option value="assignment" {{ $lesson->type == 'assignment' ? 'selected' : '' }}>Assignment</option>
                                                            </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Video URL</label>
                                                            <input type="text" class="form-control" name="video_url" value="{{ $lesson->video_url }}">
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>Duration (seconds)</label>
                                                                    <input type="number" class="form-control" name="video_duration" value="{{ $lesson->video_duration }}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>Preview</label>
                                                                    <div class="custom-control custom-checkbox mt-2">
                                                                        <input type="checkbox" class="custom-control-input" id="is_preview_edit{{ $lesson->id }}" name="is_preview" value="1" {{ $lesson->is_preview ? 'checked' : '' }}>
                                                                        <label class="custom-control-label" for="is_preview_edit{{ $lesson->id }}">Free Preview</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Content</label>
                                                            <textarea class="form-control" name="content" rows="4">{{ $lesson->content }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Update Lesson</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <li class="list-group-item text-muted text-center">No lessons yet</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>

                        <!-- Edit Section Modal -->
                        <div class="modal fade" id="editSectionModal{{ $section->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.courses.sections.update', [$course, $section]) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Section</h5>
                                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Section Title</label>
                                                <input type="text" class="form-control" name="title" value="{{ $section->title }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Description</label>
                                                <textarea class="form-control" name="description" rows="3">{{ $section->description }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Update Section</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Add Lesson Modal -->
                        <div class="modal fade" id="addLessonModal{{ $section->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="{{ route('admin.courses.sections.lessons.store', [$course, $section]) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Add Lesson to: {{ $section->title }}</h5>
                                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Title</label>
                                                <input type="text" class="form-control" name="title" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Type</label>
                                                <select class="form-control" name="type" required>
                                                    <option value="video">Video</option>
                                                    <option value="text">Text</option>
                                                    <option value="quiz">Quiz</option>
                                                    <option value="assignment">Assignment</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label>Video URL</label>
                                                <input type="text" class="form-control" name="video_url" placeholder="YouTube or Vimeo URL">
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Duration (seconds)</label>
                                                        <input type="number" class="form-control" name="video_duration" min="0">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Preview</label>
                                                        <div class="custom-control custom-checkbox mt-2">
                                                            <input type="checkbox" class="custom-control-input" id="is_preview{{ $section->id }}" name="is_preview" value="1">
                                                            <label class="custom-control-label" for="is_preview{{ $section->id }}">Free Preview</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>Content</label>
                                                <textarea class="form-control" name="content" rows="4" placeholder="Lesson content or description"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Add Lesson</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-folder-open fa-3x mb-3"></i>
                            <p>No sections yet. Click "Add Section" to get started.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Course Info -->
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Course Info</h3>
                    </div>
                    <div class="card-body">
                        @if($course->thumbnail)
                        <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" class="img-fluid rounded mb-3">
                        @endif
                        <p><strong>Status:</strong> 
                            <span class="badge badge-{{ $course->status == 'published' ? 'success' : ($course->status == 'draft' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($course->status) }}
                            </span>
                        </p>
                        <p><strong>Instructor:</strong> {{ $course->instructor->name }}</p>
                        <p><strong>Category:</strong> {{ $course->category->name }}</p>
                        <p><strong>Sections:</strong> {{ $course->sections->count() }}</p>
                        <p><strong>Lessons:</strong> {{ $course->sections->sum(fn($s) => $s->lessons->count()) }}</p>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-warning btn-block">
                            <i class="fas fa-edit"></i> Edit Course
                        </a>
                        <a href="{{ route('admin.courses.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-arrow-left"></i> Back to Courses
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Add Section Modal -->
<div class="modal fade" id="addSectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.courses.sections.store', $course) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Section</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Section Title</label>
                        <input type="text" class="form-control" name="title" required placeholder="e.g., Introduction">
                    </div>
                    <div class="form-group">
                        <label>Description (optional)</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Brief description of this section"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Section</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
