@extends('layouts.lecturer')

@section('title', 'Reviews')
@section('page-title', 'Course Reviews')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Reviews</li>
@endsection

@section('content')
<!-- Stats -->
<div class="row">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning"><i class="fas fa-star"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Average Rating</span>
                <span class="info-box-number">{{ number_format($avgRating ?? 0, 1) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info"><i class="fas fa-comments"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Reviews</span>
                <span class="info-box-number">{{ $totalReviews ?? 0 }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success"><i class="fas fa-thumbs-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">5 Star Reviews</span>
                <span class="info-box-number">{{ $fiveStarCount ?? 0 }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-danger"><i class="fas fa-thumbs-down"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">1-2 Star Reviews</span>
                <span class="info-box-number">{{ $lowRatingCount ?? 0 }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Reviews</h3>
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
                <div class="col-md-3">
                    <select name="rating" class="form-control">
                        <option value="">All Ratings</option>
                        @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} Stars</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info btn-block">Filter</button>
                </div>
            </div>
        </form>

        <!-- Reviews List -->
        @forelse($reviews as $review)
        <div class="card card-outline card-{{ $review->rating >= 4 ? 'success' : ($review->rating >= 3 ? 'warning' : 'danger') }} mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <img src="{{ $review->user->avatar ? Storage::url($review->user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($review->user->name ?? '') }}" 
                             class="img-circle mr-2" style="width: 30px; height: 30px;">
                        <strong>{{ $review->user->name ?? 'Anonymous' }}</strong>
                        <span class="text-muted ml-2">on</span>
                        <a href="{{ route('instructor.courses.edit', $review->course) }}">{{ $review->course->title ?? 'N/A' }}</a>
                    </div>
                    <div class="text-warning">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>
                        @endfor
                    </div>
                </div>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $review->comment }}</p>
            </div>
            <div class="card-footer text-muted">
                <small>{{ $review->created_at->format('M d, Y H:i') }} ({{ $review->created_at->diffForHumans() }})</small>
            </div>
        </div>
        @empty
        <div class="text-center py-5">
            <i class="fas fa-star fa-4x text-muted mb-3"></i>
            <h4>No reviews yet</h4>
            <p class="text-muted">Reviews from your students will appear here.</p>
        </div>
        @endforelse

        <!-- Pagination -->
        <div class="mt-3">
            {{ $reviews->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
