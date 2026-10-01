@extends('layouts.app')

@section('title', 'Exam Result - ' . $attempt->exam->title)

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-5xl mx-auto px-4">
        <!-- Result Header -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="px-6 py-4 {{ $attempt->passed ? 'bg-green-600' : 'bg-red-600' }} text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold mb-1">
                            <i class="fas fa-{{ $attempt->passed ? 'check-circle' : 'times-circle' }} mr-2"></i>
                            Exam Result
                        </h1>
                        <p class="text-white text-opacity-90">{{ $attempt->exam->title }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-4xl font-bold">{{ round(($attempt->score / $attempt->total_points) * 100) }}%</div>
                        <div class="text-sm text-white text-opacity-90">Your Score</div>
                    </div>
                </div>
            </div>

            <!-- Score Summary -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 p-6 bg-gray-50">
                <div class="text-center p-4 bg-white rounded-lg border-2 {{ $attempt->passed ? 'border-green-500' : 'border-red-500' }}">
                    <div class="text-3xl font-bold {{ $attempt->passed ? 'text-green-600' : 'text-red-600' }}">
                        {{ round(($attempt->score / $attempt->total_points) * 100) }}%
                    </div>
                    <div class="text-sm text-gray-600 mt-1">Your Score</div>
                </div>
                <div class="text-center p-4 bg-white rounded-lg border-2 border-gray-200">
                    <div class="text-3xl font-bold text-gray-900">{{ $attempt->score }}/{{ $attempt->total_points }}</div>
                    <div class="text-sm text-gray-600 mt-1">Points Earned</div>
                </div>
                <div class="text-center p-4 bg-white rounded-lg border-2 border-gray-200">
                    <div class="text-3xl font-bold text-gray-900">{{ $attempt->exam->passing_score }}%</div>
                    <div class="text-sm text-gray-600 mt-1">Passing Score</div>
                </div>
                <div class="text-center p-4 bg-white rounded-lg border-2 {{ $attempt->passed ? 'border-green-500' : 'border-red-500' }}">
                    <div class="text-2xl font-bold {{ $attempt->passed ? 'text-green-600' : 'text-red-600' }}">
                        {{ $attempt->passed ? 'PASSED' : 'FAILED' }}
                    </div>
                    <div class="text-sm text-gray-600 mt-1">Status</div>
                </div>
            </div>

            @if($attempt->status === 'submitted')
                <div class="mx-6 mb-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
                    <div class="flex items-start">
                        <i class="fas fa-clock text-yellow-500 mt-1 mr-3"></i>
                        <p class="text-gray-700">Some questions require manual grading. Your final score will be updated once grading is complete.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Questions Review -->
        @if($attempt->exam->show_correct_answers)
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Review Your Answers</h2>
                
                @foreach($attempt->exam->questions as $question)
                    @php
                        $answer = $attempt->answers->where('question_id', $question->id)->first();
                    @endphp
                    <div class="mb-6 p-6 border-2 rounded-lg {{ $answer && $answer->is_correct ? 'border-green-200 bg-green-50' : ($answer && $answer->is_correct === false ? 'border-red-200 bg-red-50' : 'border-gray-200') }}">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Question {{ $loop->iteration }}
                                <span class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ $question->points }} {{ Str::plural('point', $question->points) }}
                                </span>
                            </h3>
                            @if($answer && $answer->is_correct !== null)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold {{ $answer->is_correct ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    <i class="fas fa-{{ $answer->is_correct ? 'check' : 'times' }} mr-1"></i>
                                    {{ $answer->is_correct ? 'Correct' : 'Incorrect' }}
                                    ({{ $answer->points_earned ?? 0 }}/{{ $question->points }})
                                </span>
                            @elseif($question->type === 'essay')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i>
                                    Pending Review
                                </span>
                            @endif
                        </div>

                        <p class="text-gray-700 mb-4 text-base">{{ $question->question }}</p>

                        @if($question->type === 'multiple_choice')
                            <div class="space-y-2">
                                @foreach($question->options as $index => $option)
                                    <div class="flex items-start p-3 rounded-lg border-2 
                                        {{ $question->correct_answer == $index ? 'border-green-500 bg-green-50' : 'border-gray-200' }}
                                        {{ $answer && $answer->answer == $index && $answer->answer != $question->correct_answer ? 'border-red-500 bg-red-50' : '' }}">
                                        <input type="radio" disabled class="mt-1 h-4 w-4" {{ $answer && $answer->answer == $index ? 'checked' : '' }}>
                                        <span class="ml-3 flex-1 {{ $question->correct_answer == $index ? 'font-semibold text-green-700' : 'text-gray-700' }}">
                                            {{ chr(97 + $index) }}) {{ $option }}
                                            @if($question->correct_answer == $index)
                                                <i class="fas fa-check text-green-600 ml-2"></i>
                                            @endif
                                            @if($answer && $answer->answer == $index && $answer->answer != $question->correct_answer)
                                                <i class="fas fa-times text-red-600 ml-2"></i>
                                            @endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                        @elseif($question->type === 'true_false')
                            <div class="space-y-3">
                                <div class="p-3 bg-gray-50 rounded-lg">
                                    <strong class="text-gray-700">Your Answer:</strong> 
                                    <span class="ml-2 font-semibold {{ $answer && $answer->is_correct ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $answer ? ucfirst($answer->answer) : 'Not answered' }}
                                        <i class="fas fa-{{ $answer && $answer->is_correct ? 'check' : 'times' }} ml-1"></i>
                                    </span>
                                </div>
                                <div class="p-3 bg-green-50 rounded-lg border border-green-200">
                                    <strong class="text-gray-700">Correct Answer:</strong> 
                                    <span class="ml-2 font-semibold text-green-700">{{ ucfirst($question->correct_answer) }}</span>
                                </div>
                            </div>

                        @elseif($question->type === 'essay')
                            <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 mb-3">
                                <strong class="text-gray-700 block mb-2">Your Answer:</strong>
                                <p class="text-gray-800 whitespace-pre-wrap">{{ $answer ? $answer->answer : 'Not answered' }}</p>
                            </div>
                            @if($answer && $answer->feedback)
                                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                    <strong class="text-gray-700 block mb-2">
                                        <i class="fas fa-comment-dots text-blue-600 mr-1"></i>
                                        Instructor Feedback:
                                    </strong>
                                    <p class="text-gray-800">{{ $answer->feedback }}</p>
                                </div>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex flex-wrap gap-4 justify-between items-center">
                <a href="{{ route('courses.learn', $attempt->exam->course->slug) }}" 
                   class="inline-flex items-center px-6 py-3 bg-gray-600 text-white font-semibold rounded-lg hover:bg-gray-700 transition-colors duration-150">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Course
                </a>
                @if(!$attempt->passed && $attempt->exam->canUserAttempt(auth()->id()))
                    <a href="{{ route('exams.start', $attempt->exam) }}" 
                       class="inline-flex items-center px-6 py-3 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition-colors duration-150">
                        <i class="fas fa-redo mr-2"></i>
                        Retake Exam
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
