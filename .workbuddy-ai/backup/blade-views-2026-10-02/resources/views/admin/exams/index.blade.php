@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Exam Management</h2>
                <a href="{{ route('admin.exams.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Exam
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="row">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
                </div>
                <div class="card-body">
                    <form method="GET">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Course</label>
                                <select name="course_id" class="form-control">
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
                                <select name="is_published" class="form-control">
                                    <option value="">All</option>
                                    <option value="1" {{ request('is_published') === '1' ? 'selected' : '' }}>Published</option>
                                    <option value="0" {{ request('is_published') === '0' ? 'selected' : '' }}>Draft</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                                <a href="{{ route('admin.exams.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Exams Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-file-alt me-2"></i>All Exams ({{ $exams->total() }})</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Title</th>
                                    <th>Course</th>
                                    <th>Questions</th>
                                    <th>Duration</th>
                                    <th>Attempts</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($exams as $exam)
                                    <tr>
                                        <td>
                                            <strong>{{ $exam->title }}</strong>
                                            @if($exam->description)
                                            <br><small class="text-muted">{{ Str::limit($exam->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $exam->course->title }}</td>
                                        <td>
                                            <span class="badge {{ $exam->questions->count() > 0 ? 'bg-info' : 'bg-warning' }}">
                                                {{ $exam->questions->count() }} questions
                                            </span>
                                        </td>
                                        <td>{{ $exam->duration_minutes ? $exam->duration_minutes . ' min' : 'Unlimited' }}</td>
                                        <td>{{ $exam->attempts_count }}</td>
                                        <td>
                                            @if($exam->is_published)
                                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Published</span>
                                            @else
                                                <span class="badge bg-secondary"><i class="fas fa-clock me-1"></i>Draft</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.exams.edit', $exam) }}" class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('admin.exams.results', $exam) }}" class="btn btn-sm btn-info" title="View Results">
                                                    <i class="fas fa-chart-bar"></i>
                                                </a>
                                                <form action="{{ route('admin.exams.destroy', $exam) }}" method="POST" class="d-inline" 
                                                      onsubmit="return confirm('Are you sure you want to delete this exam? All questions and attempts will be deleted.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">No exams found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($exams->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $exams->firstItem() }} to {{ $exams->lastItem() }} of {{ $exams->total() }} exams
                        </div>
                        <div>
                            {{ $exams->links() }}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
