@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">📊 Exam Results</h2>
            <p class="text-muted mb-0">{{ $exam->title }} - {{ $exam->course->title }}</p>
        </div>
        <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Exams
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Attempts</h6>
                            <h2 class="mb-0">{{ $attempts->count() }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-success border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Passed</h6>
                            <h2 class="mb-0 text-success">{{ $attempts->where('passed', true)->count() }}</h2>
                            @if($attempts->count() > 0)
                            <small class="text-muted">{{ round(($attempts->where('passed', true)->count() / $attempts->count()) * 100) }}% pass rate</small>
                            @endif
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-danger border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Failed</h6>
                            <h2 class="mb-0 text-danger">{{ $attempts->where('passed', false)->count() }}</h2>
                            @if($attempts->count() > 0)
                            <small class="text-muted">{{ round(($attempts->where('passed', false)->count() / $attempts->count()) * 100) }}% fail rate</small>
                            @endif
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="fas fa-times-circle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-start border-warning border-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Average Score</h6>
                            <h2 class="mb-0 text-warning">
                                @if($attempts->count() > 0)
                                    {{ round($attempts->avg(function($attempt) { return ($attempt->score / $attempt->total_points) * 100; })) }}%
                                @else
                                    0%
                                @endif
                            </h2>
                            <small class="text-muted">Passing: {{ $exam->passing_score }}%</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-chart-line fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">📋 All Attempts</h5>
                <span class="badge bg-secondary">{{ $attempts->count() }} total</span>
            </div>
        </div>
        <div class="card-body">
            @if($attempts->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Score</th>
                            <th>Percentage</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attempts as $attempt)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px; font-weight: bold;">
                                        {{ strtoupper(substr($attempt->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <strong>{{ $attempt->user->name }}</strong>
                                        <br><small class="text-muted">{{ $attempt->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $attempt->score }}</strong><span class="text-muted">/{{ $attempt->total_points }}</span>
                            </td>
                            <td>
                                <h5 class="mb-0 {{ $attempt->passed ? 'text-success' : 'text-danger' }}">
                                    {{ round(($attempt->score / $attempt->total_points) * 100) }}%
                                </h5>
                            </td>
                            <td>
                                @if($attempt->passed)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> PASSED
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-times-circle"></i> FAILED
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($attempt->submitted_at)
                                    {{ $attempt->submitted_at->format('M d, Y') }}
                                    <br><small class="text-muted">{{ $attempt->submitted_at->format('h:i A') }}</small>
                                @else
                                    <span class="text-muted">In Progress</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('exams.result', $attempt) }}" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                @if($attempt->status === 'submitted')
                                    <a href="{{ route('admin.exams.grade', $attempt) }}" class="btn btn-sm btn-warning">
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
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No attempts yet for this exam</h5>
                <p class="text-muted">Student results will appear here once they take the exam</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
