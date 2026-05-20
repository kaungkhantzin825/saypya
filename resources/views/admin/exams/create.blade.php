@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <h2>Create New Exam</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.exams.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Course <span class="text-danger">*</span></label>
                            <select name="course_id" class="form-control @error('course_id') is-invalid @enderror" required>
                                <option value="">Select Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('course_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Exam Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                                   value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                      rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Duration (minutes)</label>
                                <input type="number" name="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror" 
                                       value="{{ old('duration_minutes') }}" min="1" placeholder="Leave empty for unlimited">
                                <small class="text-muted">Leave empty for unlimited time</small>
                                @error('duration_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Passing Score (%) <span class="text-danger">*</span></label>
                                <input type="number" name="passing_score" class="form-control @error('passing_score') is-invalid @enderror" 
                                       value="{{ old('passing_score', 70) }}" min="0" max="100" required>
                                @error('passing_score')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Max Attempts <span class="text-danger">*</span></label>
                                <input type="number" name="max_attempts" class="form-control @error('max_attempts') is-invalid @enderror" 
                                       value="{{ old('max_attempts', 1) }}" min="1" required>
                                @error('max_attempts')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="show_results" class="form-check-input" id="show_results" 
                                       {{ old('show_results', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_results">
                                    Show results immediately after submission
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="show_correct_answers" class="form-check-input" id="show_correct_answers" 
                                       {{ old('show_correct_answers', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_correct_answers">
                                    Show correct answers to students
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_published" class="form-check-input" id="is_published" 
                                       {{ old('is_published') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">
                                    Publish exam (students can take it)
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Exam
                            </button>
                            <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Note:</strong> After creating the exam, you'll be able to add questions on the edit page.</p>
                    <hr>
                    <h6>Exam Settings:</h6>
                    <ul class="small">
                        <li><strong>Duration:</strong> Time limit for students to complete the exam</li>
                        <li><strong>Passing Score:</strong> Minimum percentage required to pass</li>
                        <li><strong>Max Attempts:</strong> How many times a student can take the exam</li>
                        <li><strong>Show Results:</strong> Display score immediately after submission</li>
                        <li><strong>Show Correct Answers:</strong> Let students see which answers were correct</li>
                        <li><strong>Published:</strong> Make exam visible to enrolled students</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
