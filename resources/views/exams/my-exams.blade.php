@extends('layouts.app')

@section('title', 'My Exams')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">My Exam History</h1>
            <p class="text-gray-600 mt-1">View all your past exam attempts and results.</p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @forelse($attempts as $attempt)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-5 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors">
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h3 class="font-semibold text-gray-900">{{ $attempt->exam->title }}</h3>
                            @if($attempt->status === 'graded')
                                @if($attempt->passed)
                                    <span class="text-xs bg-green-100 text-green-700 rounded-full px-2.5 py-1 font-medium">
                                        <i class="fas fa-check-circle mr-1"></i>Passed
                                    </span>
                                @else
                                    <span class="text-xs bg-red-100 text-red-700 rounded-full px-2.5 py-1 font-medium">
                                        <i class="fas fa-times-circle mr-1"></i>Failed
                                    </span>
                                @endif
                            @elseif($attempt->status === 'submitted')
                                <span class="text-xs bg-yellow-100 text-yellow-700 rounded-full px-2.5 py-1 font-medium">
                                    <i class="fas fa-hourglass-half mr-1"></i>Pending Review
                                </span>
                            @elseif($attempt->status === 'in_progress')
                                <span class="text-xs bg-blue-100 text-blue-700 rounded-full px-2.5 py-1 font-medium">
                                    <i class="fas fa-spinner mr-1"></i>In Progress
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-gray-500 myanmar-text">
                            {{ $attempt->exam->course->title ?? 'N/A' }}
                        </p>
                        <div class="flex flex-wrap gap-3 mt-2">
                            @if($attempt->started_at)
                                <span class="text-xs text-gray-500">
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ \Carbon\Carbon::parse($attempt->started_at)->format('d M Y, h:i A') }}
                                </span>
                            @endif
                            @if($attempt->status === 'graded' && $attempt->total_points > 0)
                                <span class="text-xs text-gray-700 font-medium">
                                    <i class="fas fa-star mr-1 text-yellow-500"></i>
                                    Score: {{ $attempt->score }} / {{ $attempt->total_points }}
                                    ({{ round(($attempt->score / $attempt->total_points) * 100) }}%)
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3 sm:mt-0 sm:ml-4 flex-shrink-0">
                        @if(in_array($attempt->status, ['graded', 'submitted']))
                            <a href="{{ route('exams.result', $attempt->id) }}"
                               class="inline-flex items-center px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700 transition-colors">
                                <i class="fas fa-chart-bar mr-2"></i>View Result
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <i class="fas fa-clipboard-check text-5xl text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">No Exam Attempts Yet</h3>
                    <p class="text-gray-500">You haven't taken any exams yet. Enroll in a course to get started!</p>
                    <a href="{{ route('courses.index') }}" class="mt-4 inline-flex items-center text-teal-600 hover:text-teal-700 font-medium">
                        <i class="fas fa-book-open mr-2"></i> Browse Courses
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($attempts->hasPages())
            <div class="mt-6">
                {{ $attempts->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
