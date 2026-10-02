@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Edit Exam: {{ $exam->title }}</h2>
                @if($exam->is_published)
                    <span class="badge badge-success badge-lg">
                        <i class="fas fa-check-circle"></i> Published
                    </span>
                @else
                    <span class="badge badge-warning badge-lg">
                        <i class="fas fa-exclamation-triangle"></i> Not Published (Students can't see this)
                    </span>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="row">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    @if($exam->questions->count() === 0)
    <div class="row">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>No questions added yet!</strong> Students cannot take this exam until you add at least one question.
            </div>
        </div>
    </div>
    @endif

    @if(!$exam->is_published)
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Exam is not published.</strong> Check the "Published" checkbox below and click "Update Settings" to make this exam visible to students.
            </div>
        </div>
    </div>
    @endif

    <!-- Exam Settings -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Exam Settings</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.exams.update', $exam) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Course *</label>
                                <select name="course_id" class="form-control" required>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ $exam->course_id == $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Exam Title *</label>
                                <input type="text" name="title" class="form-control" value="{{ $exam->title }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2">{{ $exam->description }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Duration (minutes)</label>
                                <input type="number" name="duration_minutes" class="form-control" value="{{ $exam->duration_minutes }}" min="1" placeholder="Unlimited">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Passing Score (%) *</label>
                                <input type="number" name="passing_score" class="form-control" value="{{ $exam->passing_score }}" min="0" max="100" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Max Attempts *</label>
                                <input type="number" name="max_attempts" class="form-control" value="{{ $exam->max_attempts }}" min="1" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label">Total Points</label>
                                <input type="text" class="form-control" value="{{ $exam->total_points }}" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="show_results" class="form-check-input" id="show_results" {{ $exam->show_results ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_results">Show results immediately</label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="show_correct_answers" class="form-check-input" id="show_correct_answers" {{ $exam->show_correct_answers ? 'checked' : '' }}>
                                    <label class="form-check-label" for="show_correct_answers">Show correct answers</label>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_published" class="form-check-input" id="is_published" {{ $exam->is_published ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_published">Published</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Settings
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Questions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-question-circle me-2"></i>Questions ({{ $exam->questions->count() }})</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addQuestionModal">
                        <i class="fas fa-plus me-1"></i> Add Question
                    </button>
                </div>
                <div class="card-body">
                    @forelse($exam->questions as $question)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <div class="flex-grow-1">
                                    <h6>Question {{ $loop->iteration }}</h6>
                                    <p class="mb-2">{{ $question->question }}</p>
                                    <div class="mb-2">
                                        <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $question->type)) }}</span>
                                        <span class="badge bg-secondary">{{ $question->points }} points</span>
                                    </div>
                                    
                                    @if($question->type === 'multiple_choice')
                                        <div class="ms-3">
                                            @foreach($question->options as $index => $option)
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" disabled {{ $question->correct_answer == $index ? 'checked' : '' }}>
                                                    <label class="form-check-label {{ $question->correct_answer == $index ? 'text-success fw-bold' : '' }}">
                                                        {{ $option }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif($question->type === 'true_false')
                                        <div class="ms-3">
                                            <span class="badge bg-success">Correct Answer: {{ ucfirst($question->correct_answer) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <form action="{{ route('admin.exams.questions.delete', [$exam, $question]) }}" method="POST" 
                                          onsubmit="return confirm('Delete this question?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-question-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No questions added yet. Click "Add Question" to start.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Question Modal -->
<div class="modal fade" id="addQuestionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.exams.questions.add', $exam) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Question</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Question Type *</label>
                        <select name="type" id="questionType" class="form-control" required>
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="true_false">True/False</option>
                            <option value="essay">Essay</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Question *</label>
                        <textarea name="question" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Points *</label>
                        <input type="number" name="points" class="form-control" value="1" min="1" required>
                    </div>

                    <!-- Multiple Choice Options -->
                    <div id="mcqOptions" style="display:none;">
                        <label class="form-label">Options *</label>
                        <div id="optionsList">
                            <div class="input-group mb-2">
                                <input type="text" name="options[]" class="form-control" placeholder="Option 1">
                                <div class="input-group-text">
                                    <input type="radio" name="correct_answer" value="0">
                                </div>
                            </div>
                            <div class="input-group mb-2">
                                <input type="text" name="options[]" class="form-control" placeholder="Option 2">
                                <div class="input-group-text">
                                    <input type="radio" name="correct_answer" value="1">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addOption()">
                            <i class="fas fa-plus me-1"></i>Add Option
                        </button>
                        <small class="text-muted d-block mt-2">Select the radio button for the correct answer</small>
                    </div>

                    <!-- True/False Options -->
                    <div id="trueFalseOptions" style="display:none;">
                        <label class="form-label">Correct Answer *</label>
                        <select name="correct_answer_tf" class="form-control">
                            <option value="true">True</option>
                            <option value="false">False</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add Question
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('questionType').addEventListener('change', function() {
    const type = this.value;
    const mcqOptions = document.getElementById('mcqOptions');
    const trueFalseOptions = document.getElementById('trueFalseOptions');
    
    // Hide all option sections first
    mcqOptions.style.display = 'none';
    trueFalseOptions.style.display = 'none';
    
    // Show relevant section
    if (type === 'multiple_choice') {
        mcqOptions.style.display = 'block';
        // Disable true/false select
        document.querySelector('[name="correct_answer_tf"]').disabled = true;
    } else if (type === 'true_false') {
        trueFalseOptions.style.display = 'block';
        // Enable true/false select and rename it
        const tfSelect = document.querySelector('[name="correct_answer_tf"]');
        tfSelect.disabled = false;
        tfSelect.setAttribute('name', 'correct_answer');
        // Disable MCQ radios
        document.querySelectorAll('#mcqOptions input[type="radio"]').forEach(r => r.disabled = true);
    } else {
        // Essay - disable all answer inputs
        document.querySelector('[name="correct_answer_tf"]').disabled = true;
        document.querySelectorAll('#mcqOptions input[type="radio"]').forEach(r => r.disabled = true);
    }
});

// Trigger on load
document.getElementById('questionType').dispatchEvent(new Event('change'));

let optionCount = 2;
function addOption() {
    const optionsList = document.getElementById('optionsList');
    const div = document.createElement('div');
    div.className = 'input-group mb-2';
    div.innerHTML = `
        <input type="text" name="options[]" class="form-control" placeholder="Option ${optionCount + 1}">
        <div class="input-group-text">
            <input type="radio" name="correct_answer" value="${optionCount}">
        </div>
    `;
    optionsList.appendChild(div);
    optionCount++;
}
</script>
@endsection
