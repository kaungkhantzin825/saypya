@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">{{ $exam->title }}</h4>
                        @if($exam->duration_minutes)
                            <div id="timer" class="fs-5">
                                <i class="fas fa-clock"></i> <span id="timeLeft">{{ $exam->duration_minutes }}:00</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($exam->description)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> {{ $exam->description }}
                        </div>
                    @endif

                    <form action="{{ route('exams.submit', $attempt) }}" method="POST" id="examForm">
                        @csrf

                        @foreach($exam->questions as $question)
                            <div class="mb-4 p-4 border rounded">
                                <h5 class="mb-3">
                                    Question {{ $loop->iteration }}
                                    <span class="badge bg-secondary">{{ $question->points }} {{ Str::plural('point', $question->points) }}</span>
                                </h5>
                                <p class="mb-3">{{ $question->question }}</p>

                                @if($question->type === 'multiple_choice')
                                    @foreach($question->options as $index => $option)
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" 
                                                   name="question_{{ $question->id }}" 
                                                   value="{{ $index }}" 
                                                   id="q{{ $question->id }}_{{ $index }}">
                                            <label class="form-check-label" for="q{{ $question->id }}_{{ $index }}">
                                                {{ $option }}
                                            </label>
                                        </div>
                                    @endforeach

                                @elseif($question->type === 'true_false')
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" 
                                               name="question_{{ $question->id }}" 
                                               value="true" 
                                               id="q{{ $question->id }}_true">
                                        <label class="form-check-label" for="q{{ $question->id }}_true">True</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" 
                                               name="question_{{ $question->id }}" 
                                               value="false" 
                                               id="q{{ $question->id }}_false">
                                        <label class="form-check-label" for="q{{ $question->id }}_false">False</label>
                                    </div>

                                @elseif($question->type === 'essay')
                                    <textarea name="question_{{ $question->id }}" 
                                              class="form-control" 
                                              rows="6" 
                                              placeholder="Type your answer here..."></textarea>
                                @endif
                            </div>
                        @endforeach

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('courses.show', $exam->course->slug) }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('Submit your exam? You cannot change answers after submission.')">
                                <i class="fas fa-check"></i> Submit Exam
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@if($exam->duration_minutes)
<script>
let timeLeft = {{ $exam->duration_minutes * 60 }}; // Convert to seconds
const timerDisplay = document.getElementById('timeLeft');
const examForm = document.getElementById('examForm');

const countdown = setInterval(function() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    
    if (timeLeft <= 0) {
        clearInterval(countdown);
        alert('Time is up! Your exam will be submitted automatically.');
        examForm.submit();
    }
    
    timeLeft--;
}, 1000);
</script>
@endif
@endsection
