@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Exam Management</h2>
        <a href="{{ route('admin.exams.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Exam
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Course</label>
                    <select name="course_id" class="form-select">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="is_published" class="form-select">
                        <option value="">All</option>
                        <option value="1" {{ request('is_published') === '1' ? 'selected' : '' }}>Published</option>
                        <option value="0" {{ request('is_published') === '0' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Exams Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Course</th>
                            <th>Questions</th>
                            <th>Duration</th>
                            <th>Attempts</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($exams as $exam)
                            <tr>
                                <td>
                                    <strong>{{ $exam->title }}</strong>
                                    <br><small class="text-muted">{{ Str::limit($exam->description, 50) }}</small>
                                </td>
                                <td>{{ $exam->course->title }}</td>
                                <td>{{ $exam->questions->count() }} questions</td>
                                <td>{{ $exam->duration_minutes ? $exam->duration_minutes . ' min' : 'Unlimited' }}</td>
                                <td>{{ $exam->attempts_count }}</td>
                                <td>
                                    @if($exam->is_published)
                                        <span class="badge bg-success">Published</span>
                                    @else
                                        <span class="badge bg-secondary">Draft</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.exams.edit', $exam) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.exams.results', $exam) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-chart-bar"></i>
                                    </a>
                                    <form action="{{ route('admin.exams.destroy', $exam) }}" method="POST" class="d-inline" 
                                          onsubmit="return confirm('Delete this exam?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">No exams found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $exams->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
