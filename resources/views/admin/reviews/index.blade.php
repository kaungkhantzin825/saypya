@extends('layouts.admin')

@section('title', 'Reviews')
@section('page-title', 'Reviews Management')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Reviews</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Reviews</h3>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <select name="rating" class="form-control">
                        <option value="">All Ratings</option>
                        @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} Stars</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="approved" class="form-control">
                        <option value="">All Status</option>
                        <option value="1" {{ request('approved') == '1' ? 'selected' : '' }}>Approved</option>
                        <option value="0" {{ request('approved') == '0' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by course or user..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info btn-block">Filter</button>
                </div>
            </div>
        </form>

        <!-- Reviews Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                    <tr>
                        <td>{{ $review->id }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $review->user) }}">{{ $review->user->name ?? 'N/A' }}</a>
                        </td>
                        <td>
                            <a href="{{ route('admin.courses.show', $review->course) }}">{{ Str::limit($review->course->title ?? 'N/A', 25) }}</a>
                        </td>
                        <td>
                            <div class="text-warning">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>
                                @endfor
                            </div>
                        </td>
                        <td>{{ Str::limit($review->comment, 50) }}</td>
                        <td>
                            <span class="badge badge-{{ $review->is_approved ? 'success' : 'warning' }}">
                                {{ $review->is_approved ? 'Approved' : 'Pending' }}
                            </span>
                        </td>
                        <td>{{ $review->created_at->format('M d, Y') }}</td>
                        <td>
                            @if(!$review->is_approved)
                            <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm" title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            @endif
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#reviewModal{{ $review->id }}" title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                            <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>

                    <!-- Review Modal -->
                    <div class="modal fade" id="reviewModal{{ $review->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Review Details</h5>
                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Student:</strong> {{ $review->user->name ?? 'N/A' }}</p>
                                    <p><strong>Course:</strong> {{ $review->course->title ?? 'N/A' }}</p>
                                    <p><strong>Rating:</strong>
                                        <span class="text-warning">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>
                                            @endfor
                                        </span>
                                    </p>
                                    <p><strong>Comment:</strong></p>
                                    <p>{{ $review->comment }}</p>
                                    <p><strong>Date:</strong> {{ $review->created_at->format('M d, Y H:i') }}</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No reviews found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $reviews->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
