@extends('layouts.app')

@section('title', $exam->title . ' - Exam')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">
        <!-- Exam Header -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-1">{{ $exam->title }}</h1>
                        <p class="text-blue-100 text-sm">{{ $exam->course->title }}</p>
                    </div>
                    @if($exam->duration_minutes)
                        <div id="timer" class="bg-white bg-opacity-20 backdrop-blur-sm rounded-lg px-4 py-2">
                            <div class="flex items-center space-x-2 text-white">
                                <i class="fas fa-clock text-xl"></i>
                                <span id="timeLeft" class="text-2xl font-bold">{{ $exam->duration_minutes }}:00</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if($exam->description)
                <div class="bg-blue-50 border-l-4 border-blue-500 px-6 py-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                        <p class="text-gray-700">{{ $exam->description }}</p>
                    </div>
                </div>
            @endif

            <div class="px-6 py-4 bg-gray-50 border-b">
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <span><i class="fas fa-question-circle mr-2"></i>{{ $exam->questions->count() }} Questions</span>
                    <span><i class="fas fa-star mr-2"></i>Total Points: {{ $exam->total_points }}</span>
                    <span><i class="fas fa-check-circle mr-2"></i>Passing Score: {{ $exam->passing_score }}%</span>
                </div>
            </div>
        </div>

        <!-- Exam Form -->
        <form action="{{ route('exams.submit', $attempt) }}" method="POST" id="examForm">
            @csrf

            @foreach($exam->questions as $question)
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Question {{ $loop->iteration }}
                        </h3>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            {{ $question->points }} {{ Str::plural('point', $question->points) }}
                        </span>
                    </div>
                    
                    <p class="text-gray-700 mb-6 text-base leading-relaxed">{{ $question->question }}</p>

                    @if($question->type === 'multiple_choice')
                        <div class="space-y-3">
                            @foreach($question->options as $index => $option)
                                <label class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all duration-150">
                                    <input type="radio" 
                                           name="question_{{ $question->id }}" 
                                           value="{{ $index }}" 
                                           class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <span class="ml-3 text-gray-700 flex-1">{{ chr(97 + $index) }}) {{ $option }}</span>
                                </label>
                            @endforeach
                        </div>

                    @elseif($question->type === 'true_false')
                        <div class="space-y-3">
                            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all duration-150">
                                <input type="radio" 
                                       name="question_{{ $question->id }}" 
                                       value="true" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <span class="ml-3 text-gray-700 font-medium">True</span>
                            </label>
                            <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all duration-150">
                                <input type="radio" 
                                       name="question_{{ $question->id }}" 
                                       value="false" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <span class="ml-3 text-gray-700 font-medium">False</span>
                            </label>
                        </div>

                    @elseif($question->type === 'essay')
                        <textarea name="question_{{ $question->id }}" 
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-150" 
                                  rows="8" 
                                  placeholder="Type your answer here..."></textarea>
                    @endif
                </div>
            @endforeach

            <!-- Submit Section -->
            <div class="bg-white rounded-lg shadow-md p-6 sticky bottom-4">
                <div class="flex justify-between items-center">
                    <a href="{{ route('courses.learn', $exam->course->slug) }}" 
                       class="inline-flex items-center px-6 py-3 bg-white border-2 border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-100 hover:border-gray-400 transition-all duration-150">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 shadow-lg hover:shadow-xl transition-all duration-150"
                            onclick="return confirm('Submit your exam? You cannot change answers after submission.')">
                        <i class="fas fa-check-circle mr-2"></i>
                        Submit Exam
                    </button>
                </div>
            </div>
        </form>
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
    
    // Warning when 5 minutes left
    if (timeLeft === 300) {
        alert('⚠️ 5 minutes remaining!');
    }
    
    // Warning when 1 minute left
    if (timeLeft === 60) {
        alert('⚠️ 1 minute remaining!');
    }
    
    if (timeLeft <= 0) {
        clearInterval(countdown);
        alert('⏰ Time is up! Your exam will be submitted automatically.');
        examForm.submit();
    }
    
    timeLeft--;
}, 1000);
</script>
@endif
@endsection
