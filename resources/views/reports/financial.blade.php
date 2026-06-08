@extends('layouts.app')

@section('title', 'Báo cáo Tài chính - NT Logistics')

@section('content')
@php
    $cycleLabels = [
        'monthly' => 'Hàng tháng',
        'quarterly' => 'Hàng quý',
        'yearly' => 'Hàng năm',
    ];
@endphp
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold text-navy">Báo cáo Tài chính</h4>
            <p class="text-muted small">Phân tích doanh thu, chi phí và lợi nhuận hệ thống. Kỳ hiện tại: {{ $periodLabel }}</p>
        </div>
    </div>

    <div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white no-print">
        <form action="{{ route('reports.financial') }}" method="GET" class="row g-3 align-items-end">
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
                    <a href="{{ route('reports.financial', request()->query() + ['export' => $format]) }}" class="btn btn-outline-navy">{{ $label }}</a>
                @endforeach
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm bg-primary text-white h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="opacity-75 mb-2">Tổng Doanh thu</h6>
                    <h2 class="fw-bold mb-0">{{ number_format($totalRevenue) }}đ</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm bg-danger text-white h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="opacity-75 mb-2">Tổng Chi phí (Đã duyệt)</h6>
                    <h2 class="fw-bold mb-0">{{ number_format($totalExpenses) }}đ</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm bg-warning text-dark h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="opacity-75 mb-2">Chi phí cố định / tháng</h6>
                    <h2 class="fw-bold mb-0">{{ number_format($recurringMonthlyTotal) }}đ</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 rounded-4 shadow-sm bg-success text-white h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="opacity-75 mb-2">Lợi nhuận gộp</h6>
                    <h2 class="fw-bold mb-0">{{ number_format($profit) }}đ</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Chart: Monthly Revenue -->
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-navy mb-4">Biến động Doanh thu ({{ $periodLabel }})</h6>
                    <div style="height: 300px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart: Customer Share -->
        <div class="col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm h-100">
                <div class="card-body p-4 text-center">
                    <h6 class="fw-bold text-navy mb-4">Cơ cấu Khách hàng (Top 5)</h6>
                    <div style="height: 300px;">
                        <canvas id="customerChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table: Overdue Debt -->
        <div class="col-12">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-navy mb-0">Chi phí đầu vào cố định</h6>
                    <button class="btn btn-sm btn-navy no-print" data-bs-toggle="modal" data-bs-target="#recurringExpenseModal" onclick="prepareRecurringAdd()">
                        <i class="fa fa-plus me-1"></i> Thêm chi phí
                    </button>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Mã</th>
                                    <th>Tên chi phí</th>
                                    <th>Nhóm</th>
                                    <th>Chu kỳ</th>
                                    <th class="text-end">Số tiền</th>
                                    <th class="text-center no-print">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recurringExpenses as $expense)
                                    <tr>
                                        <td class="fw-bold text-navy">{{ $expense->expense_code }}</td>
                                        <td>{{ $expense->name }}</td>
                                        <td>{{ $expense->category ?? '---' }}</td>
                                        <td>{{ $cycleLabels[$expense->cycle] ?? $expense->cycle }}</td>
                                        <td class="text-end fw-bold">{{ number_format($expense->amount) }}đ</td>
                                        <td class="text-center no-print">
                                            <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#recurringExpenseModal" onclick='prepareRecurringEdit(@json($expense))'>
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <form action="{{ route('recurring-expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Xóa chi phí cố định này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Chưa có chi phí cố định.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $recurringExpenses->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h6 class="fw-bold text-danger mb-0">Cảnh báo Nợ quá hạn (> 30 ngày)</h6>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Số Báo nợ</th>
                                    <th>Khách hàng</th>
                                    <th>Ngày lập</th>
                                    <th class="text-end">Số tiền nợ</th>
                                    <th class="text-center">Số ngày quá hạn</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($overdueDebt as $debt)
                                    <tr>
                                        <td class="fw-bold text-navy">{{ $debt->note_number }}</td>
                                        <td>{{ $debt->customer_name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($debt->issued_at)->format('d/m/Y') }}</td>
                                        <td class="text-end fw-bold text-danger">{{ number_format($debt->grand_total) }}đ</td>
                                        <td class="text-center">
                                            <span class="badge bg-danger">
                                                {{ \Carbon\Carbon::parse($debt->issued_at)->diffInDays(now()) }} ngày
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('debit-notes.show', $debt->id) }}" class="btn btn-sm btn-outline-navy">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted small">Không có nợ quá hạn.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="recurringExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="recurringExpenseForm" action="{{ route('recurring-expenses.store') }}" method="POST">
                @csrf
                <div id="recurringMethodField"></div>
                <div class="modal-header bg-navy text-white border-0 rounded-top-4 p-4">
                    <h5 class="modal-title fw-bold" id="recurringModalTitle">Thêm chi phí cố định</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Tên chi phí</label>
                            <input type="text" name="name" id="recurring_name" class="form-control" data-validate required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Nhóm chi phí</label>
                            <input type="text" name="category" id="recurring_category" class="form-control" placeholder="VD: Văn phòng, kho bãi">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">Số tiền</label>
                            <input type="number" name="amount" id="recurring_amount" class="form-control" data-validate required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">Chu kỳ</label>
                            <select name="cycle" id="recurring_cycle" class="form-select" data-validate required>
                                <option value="monthly">Hàng tháng</option>
                                <option value="quarterly">Hàng quý</option>
                                <option value="yearly">Hàng năm</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted">Trạng thái</label>
                            <select name="status" id="recurring_status" class="form-select" data-validate required>
                                <option value="active">Đang sử dụng</option>
                                <option value="inactive">Tạm ngưng</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Hiệu lực từ</label>
                            <input type="text" name="effective_from" id="recurring_effective_from" class="form-control" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Hiệu lực từ">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Hiệu lực đến</label>
                            <input type="text" name="effective_to" id="recurring_effective_to" class="form-control" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Hiệu lực đến">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small text-muted">Ghi chú</label>
                            <textarea name="note" id="recurring_note" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-navy fw-bold px-4">Lưu chi phí</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function prepareRecurringAdd() {
        document.getElementById('recurringModalTitle').innerText = 'Thêm chi phí cố định';
        document.getElementById('recurringExpenseForm').action = "{{ route('recurring-expenses.store') }}";
        document.getElementById('recurringMethodField').innerHTML = '';
        document.getElementById('recurringExpenseForm').reset();
    }

    function dateOnly(value) {
        return value ? value.split('T')[0].split(' ')[0] : '';
    }

    function prepareRecurringEdit(expense) {
        document.getElementById('recurringModalTitle').innerText = 'Sửa chi phí cố định';
        document.getElementById('recurringExpenseForm').action = `/recurring-expenses/${expense.id}`;
        document.getElementById('recurringMethodField').innerHTML = '@method("PUT")';
        document.getElementById('recurring_name').value = expense.name || '';
        document.getElementById('recurring_category').value = expense.category || '';
        document.getElementById('recurring_amount').value = expense.amount || 0;
        document.getElementById('recurring_cycle').value = expense.cycle || 'monthly';
        document.getElementById('recurring_status').value = expense.status || 'active';
        document.getElementById('recurring_effective_from').value = expense.effective_from ? isoToDate(dateOnly(expense.effective_from)) : '';
        document.getElementById('recurring_effective_to').value = expense.effective_to ? isoToDate(dateOnly(expense.effective_to)) : '';
        document.getElementById('recurring_note').value = expense.note || '';
    }

    // Revenue Chart
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($monthlyRevenue->pluck('month')) !!},
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: {!! json_encode($monthlyRevenue->pluck('total')) !!},
                borderColor: '#1e3a8a',
                backgroundColor: 'rgba(30, 58, 138, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Customer Chart
    const cusCtx = document.getElementById('customerChart').getContext('2d');
    new Chart(cusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($customerRevenue->pluck('company_name')) !!},
            datasets: [{
                data: {!! json_encode($customerRevenue->pluck('total')) !!},
                backgroundColor: ['#1e3a8a', '#3b82f6', '#60a5fa', '#93c5fd', '#dbeafe']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
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
