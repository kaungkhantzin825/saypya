@extends('layouts.admin')

@section('title', 'User Details')
@section('page-title', 'User Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
<li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-4">
        <!-- Profile Card -->
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    <img class="profile-user-img img-fluid img-circle" 
                         src="{{ $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&size=128' }}" 
                         alt="User profile picture">
                </div>
                <h3 class="profile-username text-center">{{ $user->name }}</h3>
                <p class="text-muted text-center">
                    <span class="badge badge-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'lecturer' ? 'info' : 'success') }}">
                        {{ ucfirst($user->role) }}
                    </span>
                    <span class="badge badge-{{ $user->is_active ? 'success' : 'secondary' }}">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Email</b> <a class="float-right">{{ $user->email }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Phone</b> <a class="float-right">{{ $user->phone ?? 'N/A' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Country</b> <a class="float-right">{{ $user->country ?? 'N/A' }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Joined</b> <a class="float-right">{{ $user->created_at->format('M d, Y') }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>Last Login</b> <a class="float-right">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</a>
                    </li>
                </ul>

                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-block"><b>Edit User</b></a>
            </div>
        </div>

        <!-- Bio -->
        @if($user->bio)
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">About</h3>
            </div>
            <div class="card-body">
                <p>{{ $user->bio }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="col-md-8">
        @if($user->role == 'lecturer')
        <!-- Lecturer Courses -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-book mr-2"></i>Courses ({{ $user->courses->count() }})</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Status</th>
                            <th>Students</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($user->courses as $course)
                        <tr>
                            <td>
                                <a href="{{ route('admin.courses.show', $course) }}">{{ Str::limit($course->title, 40) }}</a>
                            </td>
                            <td>
                                <span class="badge badge-{{ $course->status == 'published' ? 'success' : ($course->status == 'draft' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($course->status) }}
                                </span>
                            </td>
                            <td>{{ $course->enrollments->count() }}</td>
                            <td>{{ number_format($course->enrollments->where('payment_status', 'completed')->sum('price_paid')) }} Ks</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No courses yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @elseif($user->role == 'student')
        <!-- Student Enrollments -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-graduation-cap mr-2"></i>Enrollments ({{ $user->enrollments->count() }})</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Progress</th>
                            <th>Enrolled</th>
                            <th>Amount Paid</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($user->enrollments as $enrollment)
                        <tr>
                            <td>
                                <a href="{{ route('admin.courses.show', $enrollment->course) }}">{{ Str::limit($enrollment->course->title ?? 'N/A', 40) }}</a>
                            </td>
                            <td>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-success" style="width: {{ $enrollment->progress_percentage }}%"></div>
                                </div>
                                <small>{{ $enrollment->progress_percentage }}%</small>
                            </td>
                            <td>{{ $enrollment->enrolled_at->format('M d, Y') }}</td>
                            <td>{{ number_format($enrollment->price_paid) }} Ks</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No enrollments yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Student Reviews -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-star mr-2"></i>Reviews ({{ $user->reviews->count() }})</h3>
            </div>
            <div class="card-body">
                @forelse($user->reviews as $review)
                <div class="border-bottom pb-2 mb-2">
                    <strong>{{ $review->course->title ?? 'N/A' }}</strong>
                    <div class="text-warning">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>
                        @endfor
                    </div>
                    <p class="mb-0">{{ $review->comment }}</p>
                    <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                </div>
                @empty
                <p class="text-center text-muted">No reviews yet</p>
                @endforelse
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
