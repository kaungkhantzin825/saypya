@extends('layouts.admin')

@section('title', 'Exam Results - ' . $exam->title)

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="bg-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold mb-2">📊 Exam Results</h1>
                    <p class="text-blue-100">{{ $exam->title }}</p>
                    <p class="text-blue-200 text-sm mt-1">{{ $exam->course->title }}</p>
                </div>
                <a href="{{ route('admin.exams.index') }}" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-semibold hover:bg-blue-50">
                    ← Back to Exams
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Attempts -->
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Total Attempts</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $attempts->count() }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Passed -->
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Passed</p>
                    <p class="text-3xl font-bold text-green-600">{{ $attempts->where('passed', true)->count() }}</p>
                    @if($attempts->count() > 0)
                    <p class="text-xs text-gray-500 mt-1">{{ round(($attempts->where('passed', true)->count() / $attempts->count()) * 100) }}% pass rate</p>
                    @endif
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Failed -->
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-red-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Failed</p>
                    <p class="text-3xl font-bold text-red-600">{{ $attempts->where('passed', false)->count() }}</p>
                    @if($attempts->count() > 0)
                    <p class="text-xs text-gray-500 mt-1">{{ round(($attempts->where('passed', false)->count() / $attempts->count()) * 100) }}% fail rate</p>
                    @endif
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Average Score -->
        <div class="bg-white rounded-lg shadow p-5 border-l-4 border-yellow-500">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Average Score</p>
                    <p class="text-3xl font-bold text-yellow-600">
                        @if($attempts->count() > 0)
                            {{ round($attempts->avg(function($attempt) { return ($attempt->score / $attempt->total_points) * 100; })) }}%
                        @else
                            0%
                        @endif
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Passing: {{ $exam->passing_score }}%</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-full">
                    <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 bg-gray-100 border-b">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900">📋 All Attempts</h2>
                <span class="text-sm text-gray-600">{{ $attempts->count() }} total</span>
            </div>
        </div>
        
        @if($attempts->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Percentage</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Submitted</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($attempts as $attempt)
                    <tr class="hover:bg-blue-50">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($attempt->user->name, 0, 1)) }}
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-semibold text-gray-900">{{ $attempt->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $attempt->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold text-gray-900">{{ $attempt->score }}</span>
                            <span class="text-gray-400">/{{ $attempt->total_points }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-lg font-bold {{ $attempt->passed ? 'text-green-600' : 'text-red-600' }}">
                                {{ round(($attempt->score / $attempt->total_points) * 100) }}%
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($attempt->passed)
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-800 border border-green-300">
                                    ✓ PASSED
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800 border border-red-300">
                                    ✗ FAILED
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <div>{{ $attempt->submitted_at ? $attempt->submitted_at->format('M d, Y') : 'In Progress' }}</div>
                            <div class="text-xs text-gray-400">{{ $attempt->submitted_at ? $attempt->submitted_at->format('h:i A') : '' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-2">
                                <a href="{{ route('exams.result', $attempt) }}" target="_blank" 
                                   class="px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700">
                                    👁 View
                                </a>
                                @if($attempt->status === 'submitted')
                                    <a href="{{ route('admin.exams.grade', $attempt) }}" 
                                       class="px-3 py-1 bg-yellow-500 text-white text-xs font-semibold rounded hover:bg-yellow-600">
                                        ✏ Grade
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
            <div class="text-6xl mb-4">📋</div>
            <p class="text-gray-500 text-lg font-medium">No attempts yet</p>
            <p class="text-gray-400 text-sm mt-2">Results will appear here once students take the exam</p>
        </div>
        @endif
    </div>
</div>
@endsection
