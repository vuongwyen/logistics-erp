@extends('layouts.app')

@section('title', 'Báo cáo Vận hành - NT Logistics')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold text-navy">Báo cáo Vận hành</h4>
            <p class="text-muted small">Phân tích hiệu suất đội xe, tài xế và tiến độ đơn hàng. Kỳ hiện tại: {{ $periodLabel }}</p>
        </div>
    </div>

    <div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white no-print">
        <form action="{{ route('reports.operational') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Kỳ báo cáo</label>
                <select name="period" id="filter-period" class="form-select">
                    <option value="last_6_months" {{ request('period', 'last_6_months') === 'last_6_months' ? 'selected' : '' }}>6 tháng gần nhất</option>
                    <option value="quarter" {{ request('period') === 'quarter' ? 'selected' : '' }}>Theo quý</option>
                    <option value="year" {{ request('period') === 'year' ? 'selected' : '' }}>Theo năm</option>
                </select>
            </div>
            <div class="col-md-2" id="filter-quarter-wrapper">
                <label class="form-label small fw-bold text-muted">Quý</label>
                <select name="quarter" class="form-select">
                    @for($quarter = 1; $quarter <= 4; $quarter++)
                        <option value="{{ $quarter }}" {{ (int) request('quarter', now()->quarter) === $quarter ? 'selected' : '' }}>Quý {{ $quarter }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2" id="filter-year-wrapper">
                <label class="form-label small fw-bold text-muted">Năm</label>
                <input type="number" name="year" class="form-control" value="{{ request('year', now()->year) }}">
            </div>
            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn btn-navy flex-fill">Xem báo cáo</button>
                @foreach(['xlsx' => 'Excel', 'csv' => 'CSV', 'docx' => 'Word', 'pdf' => 'PDF'] as $format => $label)
                    <a href="{{ route('reports.operational', request()->query() + ['export' => $format]) }}" class="btn btn-outline-navy">{{ $label }}</a>
                @endforeach
            </div>
        </form>
    </div>

    <div class="row g-4">
        <!-- Job Status Distribution -->
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="fw-bold text-navy mb-4">Trạng thái Đơn hàng</h6>
                    <div style="height: 300px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicle Usage -->
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold text-navy mb-0">Hiệu suất Đội xe (Top 5 xe chạy nhiều)</h6>
                        <span class="badge bg-navy">{{ $activeVehicles }}/{{ $totalVehicles }} xe đang hoạt động</span>
                    </div>
                    <div style="height: 300px;">
                        <canvas id="vehicleChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Drivers -->
        <div class="col-md-6">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h6 class="fw-bold text-navy mb-0">Top 5 Tài xế Năng suất (Chuyến hoàn thành)</h6>
                </div>
                <div class="card-body p-4">
                    <ul class="list-group list-group-flush">
                        @foreach($topDrivers as $driver)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-light">
                                <div>
                                    <i class="fa fa-user-circle text-muted me-2"></i>
                                    <span class="fw-bold">{{ $driver->full_name }}</span>
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $driver->total_jobs }} chuyến</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h6 class="fw-bold text-navy mb-0">Năng suất tài xế trong kỳ</h6>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Tài xế</th>
                                    <th class="text-end">Số chuyến</th>
                                    <th class="text-end">Số ngày chạy</th>
                                    <th class="text-end">Tổng giờ chạy</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($driverProductivity as $driver)
                                    <tr>
                                        <td class="fw-bold">{{ $driver->full_name }}</td>
                                        <td class="text-end">{{ $driver->total_trips }}</td>
                                        <td class="text-end">{{ $driver->total_days }}</td>
                                        <td class="text-end">{{ $driver->total_hours }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Chưa có chuyến hoàn thành trong kỳ.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicle Stats Table -->
        <div class="col-md-6">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h6 class="fw-bold text-navy mb-0">Phân bổ Phương tiện</h6>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Biển số</th>
                                    <th class="text-end">Tổng số chuyến</th>
                                    <th>Xu hướng</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vehicleStats as $stat)
                                    <tr>
                                        <td class="fw-bold">{{ $stat->plate_number }}</td>
                                        <td class="text-end">{{ $stat->total_trips }}</td>
                                        <td>
                                            <div class="progress" style="height: 5px;">
                                                <div class="progress-bar bg-success" style="width: {{ ($stat->total_trips / max($vehicleStats->pluck('total_trips')->toArray())) * 100 }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode($jobStatuses->pluck('status')) !!},
            datasets: [{
                data: {!! json_encode($jobStatuses->pluck('total')) !!},
                backgroundColor: ['#1e3a8a', '#3b82f6', '#10b981', '#f59e0b', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Vehicle Chart
    const vehCtx = document.getElementById('vehicleChart').getContext('2d');
    new Chart(vehCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($vehicleStats->pluck('plate_number')) !!},
            datasets: [{
                label: 'Số chuyến đi',
                data: {!! json_encode($vehicleStats->pluck('total_trips')) !!},
                backgroundColor: '#1e3a8a',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    function handlePeriodChange() {
        const period = document.getElementById('filter-period').value;
        const quarterWrapper = document.getElementById('filter-quarter-wrapper');
        const yearWrapper = document.getElementById('filter-year-wrapper');
        
        if (period === 'last_6_months') {
            quarterWrapper.style.display = 'none';
            yearWrapper.style.display = 'none';
        } else if (period === 'quarter') {
            quarterWrapper.style.display = 'block';
            yearWrapper.style.display = 'block';
        } else if (period === 'year') {
            quarterWrapper.style.display = 'none';
            yearWrapper.style.display = 'block';
        }
    }

    document.getElementById('filter-period').addEventListener('change', handlePeriodChange);
    handlePeriodChange(); // call on load
</script>
<style>
    @media print {
        .sidebar, .top-navbar, .no-print, .btn { display: none !important; }
        .main-wrapper { margin-left: 0 !important; }
    }
</style>
@endsection
