@extends('layouts.admin')

@section('title', 'Course Details')
@section('page-title', 'Course Details')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item"><a href="{{ route('admin.courses.index') }}">Courses</a></li>
<li class="breadcrumb-item active">{{ Str::limit($course->title, 30) }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Course Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $course->title }}</h3>
                <div class="card-tools">
                    <span class="badge badge-{{ $course->status == 'published' ? 'success' : ($course->status == 'draft' ? 'warning' : 'secondary') }}">
                        {{ ucfirst($course->status) }}
                    </span>
                    @if($course->is_featured)
                    <span class="badge badge-warning"><i class="fas fa-star"></i> Featured</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                @if($course->thumbnail)
                <img src="{{ Storage::url($course->thumbnail) }}" class="img-fluid mb-3" style="max-height: 300px;">
                @endif
                
                <h5>Description</h5>
                <p>{{ $course->description }}</p>

                @if($course->short_description)
                <h5>Short Description</h5>
                <p>{{ $course->short_description }}</p>
                @endif

                @if($course->what_you_learn)
                <h5>What You'll Learn</h5>
                <ul>
                    @foreach($course->what_you_learn as $item)
                    <li>{{ $item }}</li>
                    @endforeach
                </ul>
                @endif

                @if($course->requirements)
                <h5>Requirements</h5>
                <ul>
                    @foreach($course->requirements as $item)
                    <li>{{ $item }}</li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>

        <!-- Course Content -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-list mr-2"></i>Course Content</h3>
            </div>
            <div class="card-body">
                @forelse($course->sections as $section)
                <div class="card card-outline card-primary mb-2">
                    <div class="card-header">
                        <h5 class="card-title">{{ $section->title }}</h5>
                        <span class="badge badge-info float-right">{{ $section->lessons->count() }} lessons</span>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($section->lessons as $lesson)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <i class="fas fa-{{ $lesson->type == 'video' ? 'play-circle' : ($lesson->type == 'quiz' ? 'question-circle' : 'file-alt') }} mr-2"></i>
                                    {{ $lesson->title }}
                                </span>
                                <span class="text-muted">{{ $lesson->duration_minutes ?? 0 }} min</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @empty
                <p class="text-center text-muted">No content added yet</p>
                @endforelse
            </div>
        </div>

        <!-- Reviews -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-star mr-2"></i>Reviews ({{ $course->reviews->count() }})</h3>
            </div>
            <div class="card-body">
                @forelse($course->reviews as $review)
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $review->user->name ?? 'Anonymous' }}</strong>
                        <div class="text-warning">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>
                            @endfor
                        </div>
                    </div>
                    <p class="mb-1">{{ $review->comment }}</p>
                    <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                </div>
                @empty
                <p class="text-center text-muted">No reviews yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Actions</h3>
            </div>
            <div class="card-body">
                @if($course->status == 'draft')
                <form action="{{ route('admin.courses.approve', $course) }}" method="POST" class="mb-2">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-success btn-block">
                        <i class="fas fa-check mr-2"></i>Approve & Publish
                    </button>
                </form>
                @elseif($course->status == 'published')
                <form action="{{ route('admin.courses.archive', $course) }}" method="POST" class="mb-2">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-secondary btn-block">
                        <i class="fas fa-archive mr-2"></i>Archive Course
                    </button>
                </form>
                @endif

                <form action="{{ route('admin.courses.feature', $course) }}" method="POST" class="mb-2">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-{{ $course->is_featured ? 'warning' : 'outline-warning' }} btn-block">
                        <i class="fas fa-star mr-2"></i>{{ $course->is_featured ? 'Remove Featured' : 'Mark as Featured' }}
                    </button>
                </form>

                <form action="{{ route('admin.courses.destroy', $course) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Are you sure you want to delete this course?')">
                        <i class="fas fa-trash mr-2"></i>Delete Course
                    </button>
                </form>
            </div>
        </div>

        <!-- Course Stats -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Course Stats</h3>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Instructor</span>
                        <a href="{{ route('admin.users.show', $course->instructor) }}">{{ $course->instructor->name ?? 'N/A' }}</a>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Category</span>
                        <span>{{ $course->category->name ?? 'N/A' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Price</span>
                        <span>
                            @if($course->discount_price)
                            <del class="text-muted">${{ number_format($course->price, 2) }}</del>
                            ${{ number_format($course->discount_price, 2) }}
                            @else
                            ${{ number_format($course->price, 2) }}
                            @endif
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Level</span>
                        <span class="badge badge-info">{{ ucfirst($course->level) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Language</span>
                        <span>{{ $course->language }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Duration</span>
                        <span>{{ $course->duration_hours }} hours</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Students</span>
                        <span class="badge badge-success">{{ $course->enrollments->count() }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Revenue</span>
                        <span class="text-success">${{ number_format($course->enrollments->where('payment_status', 'completed')->sum('price_paid'), 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Avg Rating</span>
                        <span>
                            <i class="fas fa-star text-warning"></i>
                            {{ number_format($course->reviews->avg('rating') ?? 0, 1) }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Created</span>
                        <span>{{ $course->created_at->format('M d, Y') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Enrolled Students -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Students</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($course->enrollments->take(5) as $enrollment)
                    <li class="list-group-item">
                        <a href="{{ route('admin.users.show', $enrollment->user) }}">{{ $enrollment->user->name ?? 'N/A' }}</a>
                        <small class="float-right text-muted">{{ $enrollment->enrolled_at->diffForHumans() }}</small>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted">No students yet</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
