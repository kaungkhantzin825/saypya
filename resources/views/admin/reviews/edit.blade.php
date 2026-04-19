@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Review</h2>
        <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Reviews
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.reviews.update', $review) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Review Info -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Student:</strong> {{ $review->user->name }}<br>
                                    <strong>Email:</strong> {{ $review->user->email }}
                                </div>
                                <div class="col-md-6">
                                    <strong>Course:</strong> {{ $review->course->title }}<br>
                                    <strong>Date:</strong> {{ $review->created_at->format('M d, Y H:i') }}
                                </div>
                            </div>
                        </div>

                        <!-- Rating -->
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating <span class="text-danger">*</span></label>
                            <select name="rating" id="rating" class="form-select @error('rating') is-invalid @enderror" required>
                                <option value="">Select Rating</option>
                                @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ old('rating', $review->rating) == $i ? 'selected' : '' }}>
                                    {{ $i }} Star{{ $i > 1 ? 's' : '' }}
                                </option>
                                @endfor
                            </select>
                            @error('rating')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Comment -->
                        <div class="mb-3">
                            <label for="comment" class="form-label">Comment</label>
                            <textarea name="comment" id="comment" rows="6" class="form-control @error('comment') is-invalid @enderror" placeholder="Review comment (optional)">{{ old('comment', $review->comment) }}</textarea>
                            @error('comment')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Maximum 1000 characters</div>
                        </div>

                        <!-- Approval Status -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_approved" id="is_approved" {{ old('is_approved', $review->is_approved) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_approved">
                                    Approved (visible to public)
                                </label>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Review
                            </button>
                            <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this review?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash me-2"></i>Delete Review
                                </button>
                            </form>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Preview Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Review Preview</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <img src="{{ $review->user->avatar_url }}" alt="{{ $review->user->name }}" class="rounded-circle me-3" width="48" height="48">
                        <div>
                            <h6 class="mb-1">{{ $review->user->name }}</h6>
                            <div class="mb-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                @endfor
                            </div>
                            <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    @if($review->comment)
                    <p class="mb-0">{{ $review->comment }}</p>
                    @else
                    <p class="text-muted mb-0"><em>No comment provided</em></p>
                    @endif
                </div>
            </div>

            <!-- Course Info -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Course Information</h5>
                </div>
                <div class="card-body">
                    <img src="{{ $review->course->thumbnail_url }}" alt="{{ $review->course->title }}" class="img-fluid rounded mb-3">
                    <h6>{{ $review->course->title }}</h6>
                    <p class="text-muted small mb-2">by {{ $review->course->instructor->name }}</p>
                    <a href="{{ route('courses.show', $review->course) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-external-link-alt me-2"></i>View Course
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
