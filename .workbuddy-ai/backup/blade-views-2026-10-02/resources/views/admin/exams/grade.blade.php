@extends('layouts.admin')

@section('title', 'Grade Exam - ' . $attempt->exam->title)
@section('page-title', 'Grade Exam Attempt')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.exams.index') }}">Exams</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.exams.results', $attempt->exam) }}">Results</a></li>
<li class="breadcrumb-item active">Grade</li>
@endsection

@section('content')
<div class="container-fluid py-2">

    {{-- Info Bar --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-1">{{ $attempt->exam->title }}</h5>
                    <p class="text-muted mb-0">
                        <i class="fas fa-user mr-1"></i>
                        <strong>{{ $attempt->user->name }}</strong>
                        &nbsp;·&nbsp;
                        <i class="fas fa-book mr-1"></i>
                        {{ $attempt->exam->course->title }}
                    </p>
                </div>
                <div class="col-md-3">
                    <p class="mb-0 text-muted small">Submitted</p>
                    <strong>{{ $attempt->submitted_at ? $attempt->submitted_at->format('M d, Y h:i A') : '—' }}</strong>
                </div>
                <div class="col-md-3 text-md-right">
                    <p class="mb-0 text-muted small">Auto-graded score</p>
                    <strong>
                        {{ $attempt->answers->whereNotNull('points_earned')->sum('points_earned') }}
                        / {{ $attempt->total_points }} pts
                    </strong>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.exams.submit-grade', $attempt) }}" method="POST">
        @csrf

        @php $gradeIndex = 0; @endphp

        @foreach($attempt->answers as $answer)
        @php $question = $answer->question; @endphp

        <div class="card mb-4 shadow-sm border-{{ $question->type === 'essay' ? 'warning' : 'light' }}">
            <div class="card-header d-flex justify-content-between align-items-center
                        {{ $question->type === 'essay' ? 'bg-warning bg-opacity-10' : 'bg-light' }}">
                <span class="font-weight-bold">
                    Q{{ $loop->iteration }}.
                    <span class="badge badge-{{ $question->type === 'essay' ? 'warning' : ($question->type === 'multiple_choice' ? 'primary' : 'secondary') }} ml-1">
                        {{ ucfirst(str_replace('_', ' ', $question->type)) }}
                    </span>
                </span>
                <span class="text-muted small">{{ $question->points }} {{ Str::plural('point', $question->points) }}</span>
            </div>
            <div class="card-body">

                {{-- Question text --}}
                <p class="font-weight-semibold mb-3">{{ $question->question }}</p>

                {{-- Student answer --}}
                <div class="mb-3">
                    <label class="text-muted small text-uppercase font-weight-bold">Student Answer</label>
                    @if($question->type === 'multiple_choice')
                        @php
                            $optionIndex = (int) $answer->answer;
                            $options = $question->options ?? [];
                        @endphp
                        <div class="alert alert-{{ $answer->is_correct ? 'success' : 'danger' }} py-2 mb-0">
                            <i class="fas fa-{{ $answer->is_correct ? 'check' : 'times' }} mr-1"></i>
                            {{ isset($options[$optionIndex]) ? chr(65 + $optionIndex) . ') ' . $options[$optionIndex] : '(No answer)' }}
                            &nbsp;·&nbsp;
                            @if($answer->is_correct)
                                <strong>Correct — {{ $answer->points_earned }} pts</strong>
                            @else
                                <strong>Wrong — 0 pts</strong>
                                &nbsp;(Correct: {{ chr(65 + (int)$question->correct_answer) }}) {{ $options[(int)$question->correct_answer] ?? '' }})
                            @endif
                        </div>
                    @elseif($question->type === 'true_false')
                        <div class="alert alert-{{ $answer->is_correct ? 'success' : 'danger' }} py-2 mb-0">
                            <i class="fas fa-{{ $answer->is_correct ? 'check' : 'times' }} mr-1"></i>
                            {{ ucfirst($answer->answer ?: '(No answer)') }}
                            &nbsp;·&nbsp;
                            @if($answer->is_correct)
                                <strong>Correct — {{ $answer->points_earned }} pts</strong>
                            @else
                                <strong>Wrong — 0 pts</strong>
                                (Correct: {{ ucfirst($question->correct_answer) }})
                            @endif
                        </div>
                    @else
                        {{-- Essay --}}
                        <div class="border rounded p-3 bg-light" style="white-space: pre-wrap; min-height: 60px;">{{ $answer->answer ?: '(No answer provided)' }}</div>
                    @endif
                </div>

                {{-- Manual grading fields for essay only --}}
                @if($question->type === 'essay')
                    <input type="hidden" name="grades[{{ $gradeIndex }}][answer_id]" value="{{ $answer->id }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">
                                    Points
                                    <span class="text-muted font-weight-normal">(max {{ $question->points }})</span>
                                </label>
                                <input type="number"
                                       name="grades[{{ $gradeIndex }}][points]"
                                       class="form-control @error('grades.'.$gradeIndex.'.points') is-invalid @enderror"
                                       min="0" max="{{ $question->points }}"
                                       value="{{ old('grades.'.$gradeIndex.'.points', $answer->points_earned ?? '') }}"
                                       required>
                                @error('grades.'.$gradeIndex.'.points')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label class="font-weight-bold">Feedback <span class="text-muted font-weight-normal">(optional)</span></label>
                                <textarea name="grades[{{ $gradeIndex }}][feedback]"
                                          class="form-control" rows="2"
                                          placeholder="Write feedback for the student...">{{ old('grades.'.$gradeIndex.'.feedback', $answer->feedback ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                    @php $gradeIndex++ @endphp
                @endif

            </div>
        </div>
        @endforeach

        {{-- If no essay questions exist --}}
        @if($gradeIndex === 0)
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                This attempt has no essay questions — all answers were auto-graded. Nothing to grade manually.
            </div>
            <a href="{{ route('admin.exams.results', $attempt->exam) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to Results
            </a>
        @else
            <div class="card shadow-sm">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <a href="{{ route('admin.exams.results', $attempt->exam) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-save mr-1"></i> Save Grades
                    </button>
                </div>
            </div>
        @endif

    </form>
</div>
@endsection
