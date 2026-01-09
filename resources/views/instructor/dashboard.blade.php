@extends('layouts.lecturer')

@section('title', 'Lecturer Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
<li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total_courses'] ?? 0 }}</h3>
                <p>My Courses</p>
            </div>
            <div class="icon"><i class="fas fa-book"></i></div>
            <a href="{{ route('instructor.courses') }}" class="small-box-footer">View all <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['total_students'] ?? 0 }}</h3>
                <p>Total Students</p>
            </div>
            <div class="icon"><i class="fas fa-users"></i></div>
            <a href="{{ route('instructor.students') }}" class="small-box-footer">View all <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>${{ number_format($stats['total_revenue'] ?? 0, 2) }}</h3>
                <p>Total Earnings</p>
            </div>
            <div class="icon"><i class="fas fa-dollar-sign"></i></div>
            <a href="{{ route('instructor.earnings') }}" class="small-box-footer">View details <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ number_format($stats['average_rating'] ?? 0, 1) }}</h3>
                <p>Average Rating</p>
            </div>
            <div class="icon"><i class="fas fa-star"></i></div>
            <a href="{{ route('instructor.reviews') }}" class="small-box-footer">View reviews <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <!-- My Courses -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-book mr-2"></i>My Courses</h3>
                <div class="card-tools">
                    <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Create Course
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Status</th>
                            <th>Students</th>
                            <th>Rating</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($courses ?? [] as $course)
                        <tr>
                            <td>
                                <a href="{{ route('instructor.courses.edit', $course) }}">{{ Str::limit($course->title, 35) }}</a>
                            </td>
                            <td>
                                <span class="badge badge-{{ $course->status == 'published' ? 'success' : ($course->status == 'draft' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($course->status) }}
                                </span>
                            </td>
                            <td>{{ $course->enrollments_count ?? 0 }}</td>
                            <td>
                                <i class="fas fa-star text-warning"></i>
                                {{ number_format($course->reviews_avg_rating ?? 0, 1) }}
                            </td>
                            <td>${{ number_format($course->enrollments->where('payment_status', 'completed')->sum('price_paid') ?? 0, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">
                                No courses yet. <a href="{{ route('instructor.courses.create') }}">Create your first course!</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Reviews -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-star mr-2"></i>Recent Reviews</h3>
            </div>
            <div class="card-body">
                @forelse($recentReviews ?? [] as $review)
                <div class="border-bottom pb-2 mb-2">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $review->user->name ?? 'Anonymous' }}</strong>
                        <div class="text-warning">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }} fa-sm"></i>
                            @endfor
                        </div>
                    </div>
                    <small class="text-muted">{{ Str::limit($review->course->title ?? '', 25) }}</small>
                    <p class="mb-0 small">{{ Str::limit($review->comment, 60) }}</p>
                </div>
                @empty
                <p class="text-center text-muted">No reviews yet</p>
                @endforelse
            </div>
            @if(count($recentReviews ?? []) > 0)
            <div class="card-footer text-center">
                <a href="{{ route('instructor.reviews') }}">View all reviews</a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Recent Students -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-user-graduate mr-2"></i>Recent Students</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Progress</th>
                    <th>Enrolled</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentStudents ?? [] as $enrollment)
                <tr>
                    <td>
                        <img src="{{ $enrollment->user->avatar ? Storage::url($enrollment->user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($enrollment->user->name ?? '') }}" 
                             class="img-circle mr-2" style="width: 30px; height: 30px;">
                        {{ $enrollment->user->name ?? 'N/A' }}
                    </td>
                    <td>{{ Str::limit($enrollment->course->title ?? 'N/A', 30) }}</td>
                    <td>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" style="width: {{ $enrollment->progress_percentage }}%"></div>
                        </div>
                        <small>{{ $enrollment->progress_percentage }}%</small>
                    </td>
                    <td>{{ $enrollment->enrolled_at->diffForHumans() }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">No students yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
