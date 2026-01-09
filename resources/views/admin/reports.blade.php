@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Reports & Analytics')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Reports</li>
@endsection

@section('content')
<!-- Overview Stats -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_users'] ?? 0 }}</h3>
                <p>Total Users</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['total_courses'] ?? 0 }}</h3>
                <p>Total Courses</p>
            </div>
            <div class="icon"><i class="fas fa-book"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['total_enrollments'] ?? 0 }}</h3>
                <p>Total Enrollments</p>
            </div>
            <div class="icon"><i class="fas fa-user-graduate"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>${{ number_format($stats['total_revenue'] ?? 0, 2) }}</h3>
                <p>Total Revenue</p>
            </div>
            <div class="icon"><i class="fas fa-dollar-sign"></i></div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Revenue by Month -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Revenue by Month</h3>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- User Distribution -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>User Distribution</h3>
            </div>
            <div class="card-body">
                <canvas id="userChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Courses -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-trophy mr-2"></i>Top Courses by Enrollments</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Course</th>
                            <th>Instructor</th>
                            <th>Students</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topCourses ?? [] as $index => $course)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ Str::limit($course->title, 25) }}</td>
                            <td>{{ $course->instructor->name ?? 'N/A' }}</td>
                            <td>{{ $course->enrollments_count }}</td>
                            <td>${{ number_format($course->revenue ?? 0, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Instructors -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chalkboard-teacher mr-2"></i>Top Instructors</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Instructor</th>
                            <th>Courses</th>
                            <th>Students</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topInstructors ?? [] as $index => $instructor)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $instructor->name }}</td>
                            <td>{{ $instructor->courses_count }}</td>
                            <td>{{ $instructor->students_count ?? 0 }}</td>
                            <td>${{ number_format($instructor->revenue ?? 0, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Category Stats -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-folder mr-2"></i>Courses by Category</h3>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Courses</th>
                    <th>Students</th>
                    <th>Revenue</th>
                    <th>Avg Rating</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categoryStats ?? [] as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->courses_count }}</td>
                    <td>{{ $category->students_count ?? 0 }}</td>
                    <td>${{ number_format($category->revenue ?? 0, 2) }}</td>
                    <td>
                        <i class="fas fa-star text-warning"></i>
                        {{ number_format($category->avg_rating ?? 0, 1) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No data</td>
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
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($monthlyRevenue['labels'] ?? []) !!},
        datasets: [{
            label: 'Revenue ($)',
            data: {!! json_encode($monthlyRevenue['data'] ?? []) !!},
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

// User Distribution Chart
const userCtx = document.getElementById('userChart').getContext('2d');
new Chart(userCtx, {
    type: 'doughnut',
    data: {
        labels: ['Students', 'Lecturers', 'Admins'],
        datasets: [{
            data: [
                {{ $userDistribution['students'] ?? 0 }},
                {{ $userDistribution['lecturers'] ?? 0 }},
                {{ $userDistribution['admins'] ?? 0 }}
            ],
            backgroundColor: ['#28a745', '#17a2b8', '#dc3545']
        }]
    },
    options: {
        responsive: true
    }
});
</script>
@endpush
