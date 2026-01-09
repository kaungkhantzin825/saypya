@extends('layouts.lecturer')

@section('title', 'Earnings')
@section('page-title', 'My Earnings')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}">Dashboard</a></li>
<li class="breadcrumb-item active">Earnings</li>
@endsection

@section('content')
<!-- Stats -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>${{ number_format($totalEarnings ?? 0, 2) }}</h3>
                <p>Total Earnings</p>
            </div>
            <div class="icon"><i class="fas fa-dollar-sign"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>${{ number_format($thisMonthEarnings ?? 0, 2) }}</h3>
                <p>This Month</p>
            </div>
            <div class="icon"><i class="fas fa-calendar"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $totalSales ?? 0 }}</h3>
                <p>Total Sales</p>
            </div>
            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>${{ number_format($avgOrderValue ?? 0, 2) }}</h3>
                <p>Avg Order Value</p>
            </div>
            <div class="icon"><i class="fas fa-chart-line"></i></div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Earnings Chart -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Monthly Earnings</h3>
            </div>
            <div class="card-body">
                <canvas id="earningsChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Earnings by Course -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-book mr-2"></i>Earnings by Course</h3>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($courseEarnings ?? [] as $course)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>{{ Str::limit($course->title, 20) }}</span>
                        <span class="badge badge-success badge-pill">${{ number_format($course->earnings ?? 0, 2) }}</span>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted">No earnings yet</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-history mr-2"></i>Recent Transactions</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTransactions ?? [] as $transaction)
                <tr>
                    <td>{{ $transaction->enrolled_at->format('M d, Y H:i') }}</td>
                    <td>{{ $transaction->user->name ?? 'N/A' }}</td>
                    <td>{{ Str::limit($transaction->course->title ?? 'N/A', 30) }}</td>
                    <td class="text-success">${{ number_format($transaction->price_paid, 2) }}</td>
                    <td>
                        <span class="badge badge-{{ $transaction->payment_status == 'completed' ? 'success' : ($transaction->payment_status == 'refunded' ? 'danger' : 'warning') }}">
                            {{ ucfirst($transaction->payment_status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">No transactions yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('earningsChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthlyData['labels'] ?? []) !!},
        datasets: [{
            label: 'Earnings ($)',
            data: {!! json_encode($monthlyData['data'] ?? []) !!},
            backgroundColor: 'rgba(40, 167, 69, 0.8)',
            borderColor: 'rgb(40, 167, 69)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush
