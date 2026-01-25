@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <h2>Edit Exam: {{ $exam->title }}</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Exam Settings -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Exam Settings</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.exams.update', $exam) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Course *</label>
                    <select name="course_id" class="form-select" required>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ $exam->course_id == $course->id ? 'selected' : '' }}>
                                {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Exam Title *</label>
                    <input type="text" name="title" class="form-control" value="{{ $exam->title }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ $exam->description }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" class="form-control" value="{{ $exam->duration_minutes }}" min="1">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Passing Score (%) *</label>
                        <input type="number" name="passing_score" class="form-control" value="{{ $exam->passing_score }}" min="0" max="100" required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Max Attempts *</label>
                        <input type="number" name="max_attempts" class="form-control" value="{{ $exam->max_attempts }}" min="1" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="show_results" class="form-check-input" id="show_results" {{ $exam->show_results ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_results">Show results immediately</label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="show_correct_answers" class="form-check-input" id="show_correct_answers" {{ $exam->show_correct_answers ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_correct_answers">Show correct answers</label>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="is_published" class="form-check-input" id="is_published" {{ $exam->is_published ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_published">Published</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update Settings</button>
            </form>
        </div>
    </div>

    <!-- Questions -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Questions ({{ $exam->questions->count() }})</h5>
            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addQuestionModal">
                <i class="fas fa-plus"></i> Add Question
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
                <p class="text-muted text-center py-4">No questions added yet. Click "Add Question" to start.</p>
            @endforelse
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
                            <optgroup label="Multiple Choice Questions (MCQs)">
                                <option value="multiple_choice">Multiple Choice Questions (MCQs)</option>
                                <option value="multiple_choice">Choose the Correct Answer</option>
                                <option value="multiple_choice">Select the Best Answer</option>
                                <option value="multiple_choice">Objective Questions</option>
                                <option value="multiple_choice">Choose One Correct Option</option>
                                <option value="multiple_choice">Tick (✓) the Correct Answer</option>
                                <option value="multiple_choice">Circle the Correct Answer</option>
                                <option value="multiple_choice">Choose the Most Appropriate Answer</option>
                                <option value="multiple_choice">Select the Correct Option (A, B, C or D)</option>
                                <option value="multiple_choice">Objective Type – MCQs</option>
                            </optgroup>
                            <optgroup label="Other Question Types">
                                <option value="true_false">True/False</option>
                                <option value="essay">Essay/Writing</option>
                            </optgroup>
                        </select>
                        <small class="text-muted">All MCQ options work the same way - choose any format name you prefer</small>
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
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <input type="radio" name="correct_answer" value="0">
                                    </div>
                                </div>
                            </div>
                            <div class="input-group mb-2">
                                <input type="text" name="options[]" class="form-control" placeholder="Option 2">
                                <div class="input-group-append">
                                    <div class="input-group-text">
                                        <input type="radio" name="correct_answer" value="1">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addOption()">Add Option</button>
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
                    <button type="submit" class="btn btn-primary">Add Question</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('questionType').addEventListener('change', function() {
    const type = this.value;
    document.getElementById('mcqOptions').style.display = type === 'multiple_choice' ? 'block' : 'none';
    document.getElementById('trueFalseOptions').style.display = type === 'true_false' ? 'block' : 'none';
    
    // Update correct_answer field name
    if (type === 'true_false') {
        document.querySelector('[name="correct_answer_tf"]').setAttribute('name', 'correct_answer');
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
        <div class="input-group-append">
            <div class="input-group-text">
                <input type="radio" name="correct_answer" value="${optionCount}">
            </div>
        </div>
    `;
    optionsList.appendChild(div);
    optionCount++;
}
</script>
@endsection
