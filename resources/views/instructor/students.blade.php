@extends('layouts.lecturer')

@section('title', 'My Students')
@section('page-title', 'My Students')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Students</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Students Enrolled in My Courses</h3>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <select name="course" class="form-control">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ request('course') == $course->id ? 'selected' : '' }}>
                            {{ $course->title }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search student..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info btn-block">Filter</button>
                </div>
            </div>
        </form>

        <!-- Students Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Email</th>
                        <th>Course</th>
                        <th>Progress</th>
                        <th>Enrolled</th>
                        <th>Last Active</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments as $enrollment)
                    <tr>
                        <td>
                            <img src="{{ $enrollment->user->avatar ? Storage::url($enrollment->user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($enrollment->user->name ?? '') }}" 
                                 class="img-circle mr-2" style="width: 30px; height: 30px;">
                            {{ $enrollment->user->name ?? 'N/A' }}
                        </td>
                        <td>{{ $enrollment->user->email ?? 'N/A' }}</td>
                        <td>{{ Str::limit($enrollment->course->title ?? 'N/A', 30) }}</td>
                        <td>
                            <div class="progress progress-sm" style="width: 100px;">
                                <div class="progress-bar bg-{{ $enrollment->progress_percentage >= 100 ? 'success' : ($enrollment->progress_percentage >= 50 ? 'info' : 'warning') }}" 
                                     style="width: {{ $enrollment->progress_percentage }}%"></div>
                            </div>
                            <small>{{ $enrollment->progress_percentage }}%</small>
                            @if($enrollment->completed_at)
                            <span class="badge badge-success ml-1">Completed</span>
                            @endif
                        </td>
                        <td>{{ $enrollment->enrolled_at->format('M d, Y') }}</td>
                        <td>{{ $enrollment->last_accessed_at ? $enrollment->last_accessed_at->diffForHumans() : 'Never' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No students found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $enrollments->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
