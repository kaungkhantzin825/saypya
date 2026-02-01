@extends('layouts.admin')

@section('title', 'Courses Management')
@section('page-title', 'Courses Management')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Courses</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Courses</h3>
        <div class="card-tools">
            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create Course
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="featured" class="form-control">
                        <option value="">All</option>
                        <option value="1" {{ request('featured') == '1' ? 'selected' : '' }}>Featured</option>
                        <option value="0" {{ request('featured') == '0' ? 'selected' : '' }}>Not Featured</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search courses..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-info btn-block">Filter</button>
                </div>
            </div>
        </form>

        <!-- Courses Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Thumbnail</th>
                        <th>Title</th>
                        <th>Instructor</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Students</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                    <tr>
                        <td>
                            <img src="{{ $course->thumbnail_url }}" 
                                 class="img-thumbnail" style="max-width: 80px;">
                        </td>
                        <td>
                            <a href="{{ route('admin.courses.show', $course) }}">{{ Str::limit($course->title, 35) }}</a>
                            @if($course->is_featured)
                            <span class="badge badge-warning"><i class="fas fa-star"></i></span>
                            @endif
                        </td>
                        <td>{{ $course->instructor->name ?? 'N/A' }}</td>
                        <td>{{ $course->category->name ?? 'N/A' }}</td>
                        <td>
                            @if($course->discount_price)
                            <del class="text-muted">{{ number_format($course->price) }} Ks</del>
                            <span class="text-success">{{ number_format($course->discount_price) }} Ks</span>
                            @else
                            {{ number_format($course->price) }} Ks
                            @endif
                        </td>
                        <td>{{ $course->enrollments_count ?? $course->enrollments->count() }}</td>
                        <td>
                            <span class="badge badge-{{ $course->status == 'published' ? 'success' : ($course->status == 'draft' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($course->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.courses.show', $course) }}" class="btn btn-info btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-primary btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($course->status == 'draft')
                            <form action="{{ route('admin.courses.approve', $course) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm" title="Approve">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('admin.courses.feature', $course) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-{{ $course->is_featured ? 'warning' : 'outline-warning' }} btn-sm" title="Toggle Featured">
                                    <i class="fas fa-star"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No courses found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
            {{ $courses->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
