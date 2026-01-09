@extends('layouts.lecturer')

@section('title', 'My Courses')
@section('page-title', 'My Courses')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Courses</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All My Courses</h3>
        <div class="card-tools">
            <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create New Course
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            @forelse($courses as $course)
            <div class="col-md-6 col-lg-4">
                <div class="card card-outline card-{{ $course->status == 'published' ? 'success' : ($course->status == 'draft' ? 'warning' : 'secondary') }}">
                    <div class="card-img-top" style="height: 150px; background: url('{{ $course->thumbnail ? Storage::url($course->thumbnail) : 'https://via.placeholder.com/300x150' }}') center/cover;"></div>
                    <div class="card-body">
                        <h5 class="card-title">{{ Str::limit($course->title, 40) }}</h5>
                        <p class="card-text text-muted small">{{ Str::limit($course->short_description, 60) }}</p>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="badge badge-{{ $course->status == 'published' ? 'success' : ($course->status == 'draft' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($course->status) }}
                            </span>
                            <span class="text-muted">
                                <i class="fas fa-users"></i> {{ $course->enrollments_count ?? 0 }} students
                            </span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-success font-weight-bold">
                                @if($course->discount_price)
                                <del class="text-muted">${{ number_format($course->price, 2) }}</del>
                                ${{ number_format($course->discount_price, 2) }}
                                @else
                                ${{ number_format($course->price, 2) }}
                                @endif
                            </span>
                            <span class="text-warning">
                                <i class="fas fa-star"></i> {{ number_format($course->reviews_avg_rating ?? 0, 1) }}
                            </span>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('instructor.courses.content', $course) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-list"></i> Content
                        </a>
                        <form action="{{ route('instructor.courses.destroy', $course) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-book fa-4x text-muted mb-3"></i>
                    <h4>No courses yet</h4>
                    <p class="text-muted">Start creating your first course to share your knowledge!</p>
                    <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>Create Your First Course
                    </a>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($courses->hasPages())
        <div class="mt-3">
            {{ $courses->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
