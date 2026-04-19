@extends('layouts.admin')

@section('title', 'Exam Results - ' . $exam->title)

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Exam Results</h1>
                <p class="text-gray-600 mt-1">{{ $exam->title }} - {{ $exam->course->title }}</p>
            </div>
            <a href="{{ route('admin.exams.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Exams
            </a>
        </div>
    </div>

    <!-- Exam Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs text-gray-600">Total Attempts</p>
                    <p class="text-xl font-bold text-gray-900">{{ $attempts->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs text-gray-600">Passed</p>
                    <p class="text-xl font-bold text-gray-900">{{ $attempts->where('passed', true)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 rounded-lg">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs text-gray-600">Failed</p>
                    <p class="text-xl font-bold text-gray-900">{{ $attempts->where('passed', false)->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <i class="fas fa-chart-line text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-xs text-gray-600">Average Score</p>
                    <p class="text-xl font-bold text-gray-900">
                        @if($attempts->count() > 0)
                            {{ round($attempts->avg(function($attempt) { return ($attempt->score / $attempt->total_points) * 100; })) }}%
                        @else
                            0%
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">All Attempts</h2>
        </div>
        
        @if($attempts->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Percentage</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($attempts as $attempt)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold text-sm">
                                    {{ strtoupper(substr($attempt->user->name, 0, 1)) }}
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $attempt->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $attempt->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-900">{{ $attempt->score }}/{{ $attempt->total_points }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-semibold {{ $attempt->passed ? 'text-green-600' : 'text-red-600' }}">
                                {{ round(($attempt->score / $attempt->total_points) * 100) }}%
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if($attempt->passed)
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Passed
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i> Failed
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $attempt->submitted_at ? $attempt->submitted_at->format('M d, Y h:i A') : 'In Progress' }}
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <a href="{{ route('exams.result', $attempt) }}" target="_blank" class="text-blue-600 hover:text-blue-900 mr-3">
                                <i class="fas fa-eye"></i> View
                            </a>
                            @if($attempt->status === 'submitted')
                                <a href="{{ route('admin.exams.grade', $attempt) }}" class="text-yellow-600 hover:text-yellow-900">
                                    <i class="fas fa-edit"></i> Grade
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-6 py-12 text-center">
            <i class="fas fa-clipboard-list text-gray-400 text-5xl mb-4"></i>
            <p class="text-gray-500 text-lg">No attempts yet for this exam.</p>
        </div>
        @endif
    </div>
</div>
@endsection
