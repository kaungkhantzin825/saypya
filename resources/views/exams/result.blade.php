@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-header {{ $attempt->passed ? 'bg-success' : 'bg-danger' }} text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-{{ $attempt->passed ? 'check-circle' : 'times-circle' }}"></i>
                        Exam Result: {{ $exam->title }}
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Score Summary -->
                    <div class="row text-center mb-4">
                        <div class="col-md-3">
                            <div class="p-3 border rounded">
                                <h2 class="mb-0 {{ $attempt->passed ? 'text-success' : 'text-danger' }}">
                                    {{ $attempt->percentage }}%
                                </h2>
                                <small class="text-muted">Your Score</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded">
                                <h2 class="mb-0">{{ $attempt->score }}/{{ $attempt->total_points }}</h2>
                                <small class="text-muted">Points</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded">
                                <h2 class="mb-0">{{ $exam->passing_score }}%</h2>
                                <small class="text-muted">Passing Score</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded">
                                <h2 class="mb-0 {{ $attempt->passed ? 'text-success' : 'text-danger' }}">
                                    {{ $attempt->passed ? 'PASSED' : 'FAILED' }}
                                </h2>
                                <small class="text-muted">Status</small>
                            </div>
                        </div>
                    </div>

                    @if($attempt->status === 'submitted')
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i> Some questions require manual grading. Your final score will be updated once grading is complete.
                        </div>
                    @endif

                    <!-- Questions Review -->
                    @if($exam->show_correct_answers)
                        <h5 class="mb-3">Review Your Answers</h5>
                        @foreach($exam->questions as $question)
                            @php
                                $answer = $attempt->answers->where('question_id', $question->id)->first();
                            @endphp
                            <div class="mb-4 p-4 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h6>
                                        Question {{ $loop->iteration }}
                                        <span class="badge bg-secondary">{{ $question->points }} {{ Str::plural('point', $question->points) }}</span>
                                    </h6>
                                    @if($answer && $answer->is_correct !== null)
                                        <span class="badge {{ $answer->is_correct ? 'bg-success' : 'bg-danger' }}">
                                            {{ $answer->is_correct ? 'Correct' : 'Incorrect' }}
                                            ({{ $answer->points_earned ?? 0 }}/{{ $question->points }})
                                        </span>
                                    @elseif($question->type === 'essay')
                                        <span class="badge bg-warning">Pending Review</span>
                                    @endif
                                </div>

                                <p class="mb-3">{{ $question->question }}</p>

                                @if($question->type === 'multiple_choice')
                                    @foreach($question->options as $index => $option)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" disabled
                                                   {{ $answer && $answer->answer == $index ? 'checked' : '' }}>
                                            <label class="form-check-label 
                                                {{ $question->correct_answer == $index ? 'text-success fw-bold' : '' }}
                                                {{ $answer && $answer->answer == $index && $answer->answer != $question->correct_answer ? 'text-danger' : '' }}">
                                                {{ $option }}
                                                @if($question->correct_answer == $index)
                                                    <i class="fas fa-check text-success"></i>
                                                @endif
                                            </label>
                                        </div>
                                    @endforeach

                                @elseif($question->type === 'true_false')
                                    <div class="mb-2">
                                        <strong>Your Answer:</strong> 
                                        <span class="{{ $answer && $answer->is_correct ? 'text-success' : 'text-danger' }}">
                                            {{ $answer ? ucfirst($answer->answer) : 'Not answered' }}
                                        </span>
                                    </div>
                                    <div>
                                        <strong>Correct Answer:</strong> 
                                        <span class="text-success">{{ ucfirst($question->correct_answer) }}</span>
                                    </div>

                                @elseif($question->type === 'essay')
                                    <div class="bg-light p-3 rounded mb-2">
                                        <strong>Your Answer:</strong>
                                        <p class="mb-0 mt-2">{{ $answer ? $answer->answer : 'Not answered' }}</p>
                                    </div>
                                    @if($answer && $answer->feedback)
                                        <div class="alert alert-info mb-0">
                                            <strong>Instructor Feedback:</strong>
                                            <p class="mb-0">{{ $answer->feedback }}</p>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('courses.show', $exam->course->slug) }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back to Course
                        </a>
                        @if(!$attempt->passed && $exam->canUserAttempt(auth()->id()))
                            <a href="{{ route('exams.start', $exam) }}" class="btn btn-warning">
                                <i class="fas fa-redo"></i> Retake Exam
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
