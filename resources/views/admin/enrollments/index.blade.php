@extends('layouts.admin')

@section('title', 'Enrollments')
@section('page-title', 'Enrollments Management')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Enrollments</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Enrollments</h3>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From Date">
                </div>
                <div class="col-md-3">
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To Date">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-info">Filter</button>
                    <a href="{{ route('admin.enrollments.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>

        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fas fa-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Completed</span>
                        <span class="info-box-number">{{ $stats['completed'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Pending</span>
                        <span class="info-box-number">{{ $stats['pending'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-danger">
                    <span class="info-box-icon"><i class="fas fa-times"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Failed/Refunded</span>
                        <span class="info-box-number">{{ ($stats['failed'] ?? 0) + ($stats['refunded'] ?? 0) }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Revenue</span>
                        <span class="info-box-number">${{ number_format($stats['revenue'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrollments Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Enrolled At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments as $enrollment)
                    <tr>
                        <td>{{ $enrollment->id }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $enrollment->user) }}">{{ $enrollment->user->name ?? 'N/A' }}</a>
                        </td>
                        <td>
                            <a href="{{ route('admin.courses.show', $enrollment->course) }}">{{ Str::limit($enrollment->course->title ?? 'N/A', 30) }}</a>
                        </td>
                        <td>${{ number_format($enrollment->price_paid, 2) }}</td>
                        <td>
                            <span class="badge badge-{{ $enrollment->payment_status == 'completed' ? 'success' : ($enrollment->payment_status == 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($enrollment->payment_status) }}
                            </span>
                        </td>
                        <td>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-success" style="width: {{ $enrollment->progress_percentage }}%"></div>
                            </div>
                            <small>{{ $enrollment->progress_percentage }}%</small>
                        </td>
                        <td>{{ $enrollment->enrolled_at->format('M d, Y H:i') }}</td>
                        <td>
                            @if($enrollment->payment_status == 'pending')
                            <form action="{{ route('admin.enrollments.approve', $enrollment) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Approve this enrollment?')">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </form>
                            <form action="{{ route('admin.enrollments.reject', $enrollment) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Reject this enrollment?')">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </form>
                            @elseif($enrollment->payment_status == 'completed')
                            <form action="{{ route('admin.enrollments.refund', $enrollment) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Are you sure you want to refund this enrollment?')">
                                    <i class="fas fa-undo"></i> Refund
                                </button>
                            </form>
                            @else
                            <span class="text-muted">No actions</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No enrollments found</td>
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
