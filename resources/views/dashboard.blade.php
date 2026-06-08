@extends('layouts.app')

@section('title', 'Bảng điều khiển - NT Logistics')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold text-navy">Tổng quan vận hành</h4>
            <p class="text-muted small">Chào mừng trở lại! Dưới đây là tóm tắt hoạt động kinh doanh của bạn.</p>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm bg-navy text-white h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="p-3 bg-white bg-opacity-10 rounded-3">
                            <i class="fa fa-truck-loading fs-4"></i>
                        </div>
                        <span class="badge bg-white text-dark bg-opacity-20 rounded-pill">Tháng này</span>
                    </div>
                    <h3 class="fw-bold mb-1">{{ number_format($stats['total_jobs']) }}</h3>
                    <p class="mb-0 small opacity-75">Tổng số đơn hàng</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="p-3 bg-primary bg-opacity-10 text-primary rounded-3">
                            <i class="fa fa-hand-holding-dollar fs-4"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1 text-navy">{{ number_format($stats['total_revenue'] / 1000000, 1) }}M</h3>
                    <p class="mb-0 small text-muted">Doanh thu (VNĐ)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="p-3 bg-danger bg-opacity-10 text-danger rounded-3">
                            <i class="fa fa-receipt fs-4"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1 text-navy">{{ number_format($stats['total_expenses'] / 1000000, 1) }}M</h3>
                    <p class="mb-0 small text-muted">Chi phí (VNĐ)</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="p-3 bg-success bg-opacity-10 text-success rounded-3">
                            <i class="fa fa-chart-line fs-4"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1 text-success">+{{ number_format($stats['profit'] / 1000000, 1) }}M</h3>
                    <p class="mb-0 small text-muted">Lợi nhuận dự kiến</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Revenue Chart -->
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h6 class="fw-bold text-navy mb-0">Phân tích Tài chính (6 tháng gần nhất)</h6>
                </div>
                <div class="card-body p-4">
                    <canvas id="financeChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Jobs -->
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-navy mb-0">Đơn hàng mới nhất</h6>
                    <a href="{{ route('shipping-jobs.index') }}" class="small text-decoration-none">Xem tất cả</a>
                </div>
                <div class="card-body p-4">
                    @foreach($recentJobs as $job)
                        @php
                            $statusLabel = match($job->status) {
                                'new' => 'Mới tạo',
                                'processing' => 'Đang xử lý',
                                'dispatched' => 'Đã điều xe',
                                'completed' => 'Hoàn thành',
                                'cancelled' => 'Đã hủy',
                                default => 'Khác',
                            };
                        @endphp
                        <div class="d-flex align-items-center mb-3 p-3 bg-light rounded-3">
                            <div class="flex-grow-1">
                                <div class="fw-bold text-navy small">{{ $job->job_code }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ $job->customer->customer_name }}</div>
                            </div>
                            <span class="badge bg-white text-navy border small">{{ $statusLabel }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('financeChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [
                {
                    label: 'Doanh thu',
                    data: {!! json_encode($revenueData) !!},
                    borderColor: '#1a237e',
                    backgroundColor: 'rgba(26, 35, 126, 0.1)',
                    fill: true,
                    tension: 0.4
                },
                {
                    label: 'Chi phí',
                    data: {!! json_encode($expenseData) !!},
                    borderColor: '#d32f2f',
                    backgroundColor: 'rgba(211, 47, 47, 0.1)',
                    fill: true,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return (value / 1000000).toFixed(1) + 'M';
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection
