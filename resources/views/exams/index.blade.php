@extends('layouts.app')

@section('title', 'Exams - ' . $course->title)

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">

        <!-- Breadcrumb -->
        <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-teal-600">Home</a>
            <span>/</span>
            <a href="{{ route('courses.show', $course->slug) }}" class="hover:text-teal-600 myanmar-text">{{ $course->title }}</a>
            <span>/</span>
            <span class="text-gray-800 font-medium">Exams</span>
        </nav>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-teal-600 to-teal-700 px-6 py-5">
                <h1 class="text-2xl font-bold text-white myanmar-text">{{ $course->title }}</h1>
                <p class="text-teal-100 mt-1">Available Exams</p>
            </div>

            <div class="p-6">
                @if(session('error'))
                    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-6">
                        {{ session('error') }}
                    </div>
                @endif

                @forelse($exams as $exam)
                    <div class="border border-gray-200 rounded-xl p-5 mb-4 hover:border-teal-300 hover:shadow-sm transition-all">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">{{ $exam->title }}</h2>
                                @if($exam->description)
                                    <p class="text-gray-600 text-sm mt-1">{{ $exam->description }}</p>
                                @endif
                                <div class="flex flex-wrap gap-3 mt-3">
                                    @if($exam->duration_minutes)
                                        <span class="inline-flex items-center text-xs bg-blue-50 text-blue-700 rounded-full px-3 py-1">
                                            <i class="fas fa-clock mr-1"></i> {{ $exam->duration_minutes }} minutes
                                        </span>
                                    @else
                                        <span class="inline-flex items-center text-xs bg-gray-50 text-gray-600 rounded-full px-3 py-1">
                                            <i class="fas fa-infinity mr-1"></i> No time limit
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center text-xs bg-green-50 text-green-700 rounded-full px-3 py-1">
                                        <i class="fas fa-percentage mr-1"></i> Pass: {{ $exam->passing_score }}%
                                    </span>
                                    <span class="inline-flex items-center text-xs bg-purple-50 text-purple-700 rounded-full px-3 py-1">
                                        <i class="fas fa-redo mr-1"></i> Max {{ $exam->max_attempts }} attempt(s)
                                    </span>
                                    <span class="inline-flex items-center text-xs bg-orange-50 text-orange-700 rounded-full px-3 py-1">
                                        <i class="fas fa-question-circle mr-1"></i> {{ $exam->questions->count() }} questions
                                    </span>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                @if($exam->canUserAttempt(auth()->id()))
                                    <a href="{{ route('exams.start', $exam->id) }}"
                                       class="inline-flex items-center px-6 py-2.5 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700 transition-colors">
                                        <i class="fas fa-play mr-2"></i> Start Exam
                                    </a>
                                @else
                                    <span class="inline-flex items-center px-6 py-2.5 bg-gray-200 text-gray-500 font-semibold rounded-lg cursor-not-allowed">
                                        <i class="fas fa-lock mr-2"></i> Max Attempts Reached
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12">
                        <i class="fas fa-clipboard-list text-5xl text-gray-300 mb-4"></i>
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">No Exams Available</h3>
                        <p class="text-gray-500">There are no published exams for this course yet.</p>
                        <a href="{{ route('courses.learn', $course->slug) }}" class="mt-4 inline-flex items-center text-teal-600 hover:text-teal-700 font-medium">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Course
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="mt-4 text-center">
            <a href="{{ route('courses.learn', $course->slug) }}" class="text-teal-600 hover:text-teal-700 font-medium">
                <i class="fas fa-arrow-left mr-2"></i> Back to Course
            </a>
        </div>
    </div>
</div>
@endsection
