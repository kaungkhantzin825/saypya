@extends('layouts.admin')

@section('title', 'Exam Results - ' . $exam->title)

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Header with Gradient -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold mb-2">📊 Exam Results</h1>
                    <p class="text-indigo-100 text-lg">{{ $exam->title }}</p>
                    <p class="text-indigo-200 text-sm mt-1">{{ $exam->course->title }}</p>
                </div>
                <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center px-5 py-2.5 bg-white text-indigo-600 font-semibold rounded-lg hover:bg-indigo-50 transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Exams
                </a>
            </div>
        </div>
    </div>

    <!-- Exam Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
        <!-- Total Attempts -->
        <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow p-5 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Attempts</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $attempts->count() }}</p>
                </div>
                <div class="p-4 bg-blue-100 rounded-full">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Passed -->
        <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow p-5 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Passed</p>
                    <p class="text-3xl font-bold text-green-600">{{ $attempts->where('passed', true)->count() }}</p>
                </div>
                <div class="p-4 bg-green-100 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
            </div>
            @if($attempts->count() > 0)
            <div class="mt-2 text-xs text-gray-500">
                {{ round(($attempts->where('passed', true)->count() / $attempts->count()) * 100) }}% pass rate
            </div>
            @endif
        </div>

        <!-- Failed -->
        <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow p-5 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Failed</p>
                    <p class="text-3xl font-bold text-red-600">{{ $attempts->where('passed', false)->count() }}</p>
                </div>
                <div class="p-4 bg-red-100 rounded-full">
                    <i class="fas fa-times-circle text-red-600 text-2xl"></i>
                </div>
            </div>
            @if($attempts->count() > 0)
            <div class="mt-2 text-xs text-gray-500">
                {{ round(($attempts->where('passed', false)->count() / $attempts->count()) * 100) }}% fail rate
            </div>
            @endif
        </div>

        <!-- Average Score -->
        <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow p-5 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Average Score</p>
                    <p class="text-3xl font-bold text-yellow-600">
                        @if($attempts->count() > 0)
                            {{ round($attempts->avg(function($attempt) { return ($attempt->score / $attempt->total_points) * 100; })) }}%
                        @else
                            0%
                        @endif
                    </p>
                </div>
                <div class="p-4 bg-yellow-100 rounded-full">
                    <i class="fas fa-chart-line text-yellow-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                Passing score: {{ $exam->passing_score }}%
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-list-alt text-indigo-600 mr-2"></i>
                    All Attempts
                </h2>
                <span class="text-sm text-gray-600">{{ $attempts->count() }} total</span>
            </div>
        </div>
        
        @if($attempts->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Score</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Percentage</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Submitted</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($attempts as $attempt)
                    <tr class="hover:bg-indigo-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                                    {{ strtoupper(substr($attempt->user->name, 0, 1)) }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-semibold text-gray-900">{{ $attempt->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $attempt->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-900">{{ $attempt->score }}<span class="text-gray-400">/{{ $attempt->total_points }}</span></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="text-lg font-bold {{ $attempt->passed ? 'text-green-600' : 'text-red-600' }}">
                                    {{ round(($attempt->score / $attempt->total_points) * 100) }}%
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($attempt->passed)
                                <span class="inline-flex items-center px-3 py-1.5 text-xs font-bold rounded-full bg-green-100 text-green-800 border border-green-200">
                                    <i class="fas fa-check-circle mr-1.5"></i> PASSED
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1.5 text-xs font-bold rounded-full bg-red-100 text-red-800 border border-red-200">
                                    <i class="fas fa-times-circle mr-1.5"></i> FAILED
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                {{ $attempt->submitted_at ? $attempt->submitted_at->format('M d, Y') : 'In Progress' }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $attempt->submitted_at ? $attempt->submitted_at->format('h:i A') : '' }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('exams.result', $attempt) }}" target="_blank" 
                                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                                    <i class="fas fa-eye mr-1.5"></i> View
                                </a>
                                @if($attempt->status === 'submitted')
                                    <a href="{{ route('admin.exams.grade', $attempt) }}" 
                                       class="inline-flex items-center px-3 py-1.5 bg-yellow-500 text-white text-xs font-semibold rounded-lg hover:bg-yellow-600 transition-colors shadow-sm">
                                        <i class="fas fa-edit mr-1.5"></i> Grade
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-6 py-16 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-4">
                <i class="fas fa-clipboard-list text-gray-400 text-4xl"></i>
            </div>
            <p class="text-gray-500 text-lg font-medium">No attempts yet for this exam</p>
            <p class="text-gray-400 text-sm mt-2">Student results will appear here once they take the exam</p>
        </div>
        @endif
    </div>
</div>
@endsection
