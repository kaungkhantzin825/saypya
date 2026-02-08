@extends('layouts.lecturer')

@section('title', 'Analytics')
@section('page-title', 'Course Analytics')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Analytics</li>
@endsection

@section('content')
<!-- Overview Stats -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-eye"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Views</span>
                <span class="info-box-number">{{ number_format($totalViews ?? 0) }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-user-plus"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">New Students (30d)</span>
                <span class="info-box-number">{{ $newStudents ?? 0 }}</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-percentage"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Avg Completion Rate</span>
                <span class="info-box-number">{{ number_format($avgCompletionRate ?? 0, 1) }}%</span>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-star"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Avg Rating</span>
                <span class="info-box-number">{{ number_format($avgRating ?? 0, 1) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Enrollment Trend -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Enrollment Trend (Last 30 Days)</h3>
            </div>
            <div class="card-body">
                <canvas id="enrollmentChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Course Performance -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-trophy mr-2"></i>Top Performing Courses</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($topCourses ?? [] as $course)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <span>{{ Str::limit($course->title, 20) }}</span>
                            <span class="badge badge-primary">{{ $course->enrollments_count }} students</span>
                        </div>
                        <div class="progress progress-sm mt-1">
                            <div class="progress-bar bg-success" style="width: {{ min(($course->enrollments_count / max($topCourses->max('enrollments_count'), 1)) * 100, 100) }}%"></div>
                        </div>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted">No data</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Course Details Table -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-table mr-2"></i>Course Performance Details</h3>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Students</th>
                    <th>Completion Rate</th>
                    <th>Avg Progress</th>
                    <th>Rating</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courseStats ?? [] as $course)
                <tr>
                    <td>{{ Str::limit($course->title, 35) }}</td>
                    <td>{{ $course->enrollments_count ?? 0 }}</td>
                    <td>
                        <div class="progress progress-sm" style="width: 80px;">
                            <div class="progress-bar bg-success" style="width: {{ $course->completion_rate ?? 0 }}%"></div>
                        </div>
                        <small>{{ number_format($course->completion_rate ?? 0, 1) }}%</small>
                    </td>
                    <td>{{ number_format($course->avg_progress ?? 0, 1) }}%</td>
                    <td>
                        <i class="fas fa-star text-warning"></i>
                        {{ number_format($course->avg_rating ?? 0, 1) }}
                        <small class="text-muted">({{ $course->reviews_count ?? 0 }})</small>
                    </td>
                    <td class="text-success">{{ number_format($course->revenue ?? 0) }} Ks</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No courses yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('enrollmentChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($enrollmentTrend['labels'] ?? []) !!},
        datasets: [{
            label: 'New Enrollments',
            data: {!! json_encode($enrollmentTrend['data'] ?? []) !!},
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush
