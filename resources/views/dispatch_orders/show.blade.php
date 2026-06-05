@extends('layouts.app')

@section('title', 'Chi tiết Lệnh điều xe ' . $dispatchOrder->order_number)

@section('content')
@php
    $loadingPercent = (int) ($dispatchOrder->loading_percent ?? 0);
    $progressClass = $loadingPercent >= 100 ? 'bg-success' : ($loadingPercent >= 70 ? 'bg-info' : ($loadingPercent >= 35 ? 'bg-warning' : 'bg-danger'));
    $mapLat = $dispatchOrder->current_latitude ?? 10.7769;
    $mapLng = $dispatchOrder->current_longitude ?? 106.7009;
@endphp

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <a href="{{ route('dispatch-orders.index') }}" class="text-navy text-decoration-none small fw-bold">
            <i class="fa fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
        <h4 class="fw-bold mt-2">Lệnh Điều Xe: {{ $dispatchOrder->order_number }}</h4>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-navy fw-bold px-4">
            <i class="fa fa-print me-2"></i> IN PHIẾU ĐIỀU XE
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- Main Info -->
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-header bg-white border-0 p-4 pb-0">
                <h5 class="fw-bold text-navy mb-0">Thông tin hành trình</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-12">
                        <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center border">
                            <div class="text-center flex-grow-1">
                                <label class="small text-muted text-uppercase d-block mb-1">Điểm Đi</label>
                                <span class="fw-bold fs-6 text-navy">{{ $dispatchOrder->shippingJob->pickupLocation->location_name }}</span>
                                <div class="small text-muted">{{ $dispatchOrder->shippingJob->pickupLocation->address }}</div>
                            </div>
                            <div class="px-4">
                                <i class="fa fa-truck text-navy fs-3"></i>
                            </div>
                            <div class="text-center flex-grow-1">
                                <label class="small text-muted text-uppercase d-block mb-1">Điểm Đến</label>
                                <span class="fw-bold fs-6 text-navy">{{ $dispatchOrder->shippingJob->deliveryLocation->location_name }}</span>
                                <div class="small text-muted">{{ $dispatchOrder->shippingJob->deliveryLocation->address }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="small text-muted text-uppercase fw-bold">Tài Xế Thực Hiện</label>
                        <div class="d-flex align-items-center mt-2">
                            <div class="bg-navy text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                <i class="fa fa-user"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-navy fs-5">{{ $dispatchOrder->driver->full_name }}</div>
                                <div class="small text-muted">GPLX: {{ $dispatchOrder->driver->license_number }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="small text-muted text-uppercase fw-bold">Phương Tiện</label>
                        <div class="d-flex align-items-center mt-2">
                            <div class="bg-navy text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                <i class="fa fa-truck-moving"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-navy fs-5">{{ $dispatchOrder->vehicle->plate_number }}</div>
                                <div class="small text-muted">{{ $dispatchOrder->vehicle->vehicle_type }} - {{ $dispatchOrder->vehicle->payload }} Tấn</div>
                                @if($dispatchOrder->trailer)
                                    <div class="small text-muted">Mooc: {{ $dispatchOrder->trailer->plate_number }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="small text-muted text-uppercase fw-bold">Địa điểm bắt đầu</label>
                                <div class="fw-bold text-navy">{{ $dispatchOrder->startLocation->location_name ?? $dispatchOrder->shippingJob->pickupLocation->location_name }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted text-uppercase fw-bold">Địa điểm kết thúc</label>
                                <div class="fw-bold text-navy">{{ $dispatchOrder->endLocation->location_name ?? $dispatchOrder->shippingJob->deliveryLocation->location_name }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="small text-muted text-uppercase fw-bold">Ngày đi</label>
                                <div class="fw-bold text-navy">{{ $dispatchOrder->planned_departure_date?->format('d/m/Y') ?? '---' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted text-uppercase fw-bold">Ngày về</label>
                                <div class="fw-bold text-navy">{{ $dispatchOrder->planned_return_date?->format('d/m/Y') ?? '---' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted text-uppercase fw-bold">Dầu thực tế</label>
                                <div class="fw-bold text-navy">{{ $dispatchOrder->actual_fuel_liters ? number_format($dispatchOrder->actual_fuel_liters, 1) . ' lít' : '---' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <label class="small text-muted text-uppercase fw-bold">Tình trạng loading</label>
                        <div class="progress mt-2" style="height: 18px;">
                            <div class="progress-bar {{ $progressClass }} fw-bold" style="width: {{ $loadingPercent }}%;">
                                {{ $loadingPercent }}%
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <label class="small text-muted text-uppercase fw-bold">Ghi chú từ điều vận</label>
                        <div class="p-3 bg-light rounded-3 italic text-muted border-start border-4 border-navy mt-2">
                            {{ $dispatchOrder->note ?? 'Không có ghi chú.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expenses Section -->
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-navy mb-0">Chi phí phát sinh (Chi hộ)</h5>
                @if(Auth::user()->hasRole(['ADMIN', 'ACCOUNTANT', 'DISPATCH', 'FIELD']))
                <button class="btn btn-navy btn-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                    <i class="fa fa-plus-circle me-1"></i> THÊM CHI PHÍ
                </button>
                @endif
            </div>
            <div class="card-body p-4">
                @if($dispatchOrder->expenses->isEmpty())
                    <div class="text-center py-4 text-muted small">
                        <i class="fa fa-receipt d-block fs-3 mb-2 opacity-50"></i>
                        Chưa có khoản chi phí nào.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr class="small text-muted">
                                    <th>Loại phí</th>
                                    <th>Ghi chú</th>
                                    <th class="text-end">Số tiền</th>
                                    <th>Người báo</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dispatchOrder->expenses as $expense)
                                    <tr>
                                        <td class="fw-bold small">{{ $expense->expense_type }}</td>
                                        <td class="small text-muted">{{ $expense->note }}</td>
                                        <td class="text-end fw-bold text-danger">{{ number_format($expense->amount) }}đ</td>
                                        <td>
                                            <span class="small text-muted">{{ $expense->reporter->name ?? '' }}</span>
                                        </td>
                                        <td class="text-end">
                                            @if(Auth::user()->hasRole(['ADMIN', 'ACCOUNTANT']))
                                            <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" id="delete-expense-{{ $expense->id }}" class="d-none">
                                                @csrf @method('DELETE')
                                            </form>
                                            <button type="button" class="btn btn-link text-danger p-0" onclick="handleDelete('delete-expense-{{ $expense->id }}', 'Xóa khoản chi phí này?')">
                                                <i class="fa fa-times-circle"></i>
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Cash Advance Section -->
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-navy mb-0">Tạm ứng đi đường</h5>
                @if(Auth::user()->hasRole(['ADMIN', 'DISPATCH', 'FIELD']))
                <button class="btn btn-outline-navy btn-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#addAdvanceModal">
                    <i class="fa fa-hand-holding-dollar me-1"></i> YÊU CẦU TẠM ỨNG
                </button>
                @endif
            </div>
            <div class="card-body p-4">
                @if($dispatchOrder->cashAdvances->isEmpty())
                    <div class="text-center py-3 text-muted small">Chưa có yêu cầu tạm ứng.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr class="small text-muted">
                                    <th>Lý do</th>
                                    <th class="text-end">Số tiền</th>
                                    <th>Trạng thái</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dispatchOrder->cashAdvances as $advance)
                                    <tr>
                                        <td class="small">{{ $advance->reason }}</td>
                                        <td class="text-end fw-bold text-primary">{{ number_format($advance->amount) }}đ</td>
                                        <td>
                                            <span class="badge {{ $advance->status == 'approved' ? 'bg-success' : ($advance->status == 'rejected' ? 'bg-danger' : 'bg-info text-dark') }} small">
                                                {{ $advance->status == 'approved' ? 'Đã chi' : ($advance->status == 'rejected' ? 'Từ chối' : 'Chờ duyệt') }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if($advance->status == 'pending' && Auth::user()->hasRole(['ADMIN', 'ACCOUNTANT']))
                                                <form action="{{ route('cash-advances.approve', $advance->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success py-0 px-2">Duyệt</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-header bg-white border-0 p-4 pb-0">
                <h5 class="fw-bold text-navy mb-0">Bản đồ vị trí xe</h5>
            </div>
            <div class="card-body p-4">
                <div id="dispatchMap" class="rounded-3 border" style="height: 360px;"></div>
                <div class="small text-muted mt-2">
                    Tọa độ hiện tại: {{ $dispatchOrder->current_latitude ?? 'chưa cập nhật' }}, {{ $dispatchOrder->current_longitude ?? 'chưa cập nhật' }}
                </div>
            </div>
        </div>

        <!-- Tracking Timeline -->
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white border-0 p-4 pb-0">
                <h5 class="fw-bold text-navy mb-0">Nhật ký hành trình</h5>
            </div>
            <div class="card-body p-4">
                <div class="timeline-custom">
                    @forelse($dispatchOrder->trackingLogs()->with('updater')->orderBy('created_at', 'desc')->get() as $log)
                        <div class="timeline-item d-flex gap-3 mb-4">
                            <div class="timeline-marker text-primary pt-1">
                                <i class="fa fa-circle-dot"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="fw-bold text-navy">
                                    @php
                                        $statusText = match($log->status_update) {
                                            'pending_approval' => 'Lập lệnh và gửi kế toán duyệt',
                                            'approved' => 'Kế toán đã duyệt lệnh',
                                            'dispatched' => 'Đã điều xe',
                                            'on_way' => 'Bắt đầu khởi hành',
                                            'completed' => 'Hoàn thành chuyến xe',
                                            default => $log->status_update
                                        };
                                    @endphp
                                    {{ $statusText }}
                                </div>
                                <div class="small text-muted">
                                    {{ $log->created_at->format('d/m/Y') }}
                                    <span class="mx-1">•</span> 
                                    Cập nhật bởi: {{ $log->updater->name }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <i class="fa fa-info-circle me-1"></i> Chưa có ghi nhận hành trình thực tế.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <style>
            .timeline-custom { position: relative; padding-left: 5px; }
            .timeline-custom::before {
                content: '';
                position: absolute;
                left: 10px;
                top: 0;
                bottom: 0;
                width: 2px;
                background: #e9ecef;
            }
            .timeline-marker { position: relative; z-index: 1; background: #fff; }
        </style>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold text-navy mb-3">Liên kết đơn hàng</h6>
                <div class="p-3 rounded-3 border bg-light mb-3">
                    <label class="small text-muted d-block">Mã Job:</label>
                    <a href="{{ route('shipping-jobs.show', $dispatchOrder->shipping_job_id) }}" class="fw-bold text-navy text-decoration-none fs-5">
                        {{ $dispatchOrder->shippingJob->job_code }} <i class="fa fa-external-link small ms-1"></i>
                    </a>
                </div>
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">Khách hàng:</span>
                        <span class="fw-bold">{{ $dispatchOrder->shippingJob->customer->customer_name }}</span>
                    </li>
                    <li class="mb-2 d-flex justify-content-between">
                        <span class="text-muted">Loại hàng:</span>
                        <span class="fw-bold">{{ $dispatchOrder->shippingJob->cargo_type }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Container:</span>
                        <span class="fw-bold">{{ $dispatchOrder->shippingJob->container_number ?? 'Lẻ' }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-body p-4">
                <h6 class="fw-bold text-navy mb-3">Thông tin lệnh</h6>
                <div class="mb-3">
                    <label class="small text-muted d-block">Trạng thái hiện tại:</label>
                    @php
                        $statusName = match(true) {
                            $dispatchOrder->approval_status === 'pending' => 'Chờ duyệt',
                            $dispatchOrder->approval_status === 'rejected' => 'Từ chối',
                            $dispatchOrder->dispatch_status === 'on_way' => 'Đang đi',
                            $dispatchOrder->dispatch_status === 'completed' => 'Hoàn thành',
                            default => 'Đã duyệt'
                        };
                        $statusClass = match(true) {
                            $dispatchOrder->approval_status === 'pending' => 'bg-warning text-dark',
                            $dispatchOrder->approval_status === 'rejected' => 'bg-danger',
                            $dispatchOrder->dispatch_status === 'on_way' => 'bg-info text-dark',
                            $dispatchOrder->dispatch_status === 'completed' => 'bg-success',
                            default => 'bg-primary'
                        };
                    @endphp
                    <span class="badge {{ $statusClass }} fs-6">{{ $statusName }}</span>
                </div>

                <div class="mb-3">
                    <label class="small text-muted d-block">Trạng thái duyệt:</label>
                    @php
                        $approvalClass = match($dispatchOrder->approval_status) {
                            'approved' => 'bg-success',
                            'rejected' => 'bg-danger',
                            default => 'bg-warning text-dark'
                        };
                        $approvalName = match($dispatchOrder->approval_status) {
                            'approved' => 'Đã duyệt',
                            'rejected' => 'Từ chối',
                            default => 'Chờ duyệt'
                        };
                    @endphp
                    <span class="badge {{ $approvalClass }} fs-6">{{ $approvalName }}</span>
                    @if($dispatchOrder->approver)
                        <div class="small text-muted mt-1">Người duyệt: {{ $dispatchOrder->approver->name }}</div>
                    @endif
                </div>

                @if(Auth::user()->hasRole(['ADMIN', 'ACCOUNTANT']) && $dispatchOrder->approval_status === 'pending')
                    <div class="d-grid gap-2 mb-4">
                        <form action="{{ route('dispatch-orders.approve', $dispatchOrder) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 fw-bold">Duyệt lệnh điều xe</button>
                        </form>
                        <form action="{{ route('dispatch-orders.reject', $dispatchOrder) }}" method="POST">
                            @csrf
                            <input type="hidden" name="rejection_reason" value="Kế toán từ chối duyệt">
                            <button type="submit" class="btn btn-outline-danger w-100 fw-bold">Từ chối</button>
                        </form>
                    </div>
                @endif

                <!-- Update Status Buttons for Drivers -->
                <div class="mt-4 pt-3 border-top">
                    @if($dispatchOrder->approval_status === 'approved')
                    <form action="{{ route('dispatch-orders.update-status', $dispatchOrder) }}" method="POST" class="mb-3">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $dispatchOrder->dispatch_status }}">
                        <label class="small text-muted d-block mb-2">Cập nhật loading & vị trí xe:</label>
                        <div class="row g-2">
                            <div class="col-12">
                                <input type="range" name="loading_percent" class="form-range" min="0" max="100" value="{{ $loadingPercent }}" oninput="document.getElementById('loadingValue').innerText = this.value + '%'">
                                <div class="small fw-bold text-navy" id="loadingValue">{{ $loadingPercent }}%</div>
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.0000001" name="current_latitude" class="form-control form-control-sm" value="{{ $dispatchOrder->current_latitude }}" placeholder="Vĩ độ">
                            </div>
                            <div class="col-6">
                                <input type="number" step="0.0000001" name="current_longitude" class="form-control form-control-sm" value="{{ $dispatchOrder->current_longitude }}" placeholder="Kinh độ">
                            </div>
                            <div class="col-12">
                                <input type="number" step="0.1" name="actual_fuel_liters" class="form-control form-control-sm" value="{{ $dispatchOrder->actual_fuel_liters }}" placeholder="Tổng dầu thực tế (lít)">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-sm btn-outline-navy w-100 fw-bold">Cập nhật tiến độ</button>
                            </div>
                        </div>
                    </form>
                    @endif

                    @if($dispatchOrder->approval_status !== 'approved')
                        <div class="alert alert-warning small mb-0">Lệnh điều xe đang chờ kế toán duyệt nên chưa thể cập nhật hành trình.</div>
                    @elseif($dispatchOrder->dispatch_status === 'dispatched')
                        <form action="{{ route('dispatch-orders.update-status', $dispatchOrder) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="on_way">
                            <button type="submit" class="btn btn-warning w-100 fw-bold py-3 rounded-3 shadow-sm">
                                <i class="fa fa-play me-2"></i> BẮT ĐẦU CHUYẾN XE
                            </button>
                        </form>
                    @elseif($dispatchOrder->dispatch_status === 'on_way')
                        <form action="{{ route('dispatch-orders.update-status', $dispatchOrder) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="btn btn-success w-100 fw-bold py-3 rounded-3 shadow-sm">
                                <i class="fa fa-check-circle me-2"></i> HOÀN THÀNH CHUYẾN
                            </button>
                        </form>
                    @else
                        <div class="text-center py-2 text-success fw-bold">
                            <i class="fa fa-circle-check"></i> Chuyến xe đã hoàn tất
                        </div>
                    @endif
                </div>

                <div class="mt-4">
                    <label class="small text-muted d-block">Thời gian bắt đầu:</label>
                    <span class="fw-bold">{{ $dispatchOrder->start_time ? \Carbon\Carbon::parse($dispatchOrder->start_time)->format('d/m/Y') : '---' }}</span>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Thời gian kết thúc:</label>
                    <span class="fw-bold">{{ $dispatchOrder->end_time ? \Carbon\Carbon::parse($dispatchOrder->end_time)->format('d/m/Y') : '---' }}</span>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Thời gian lập lệnh:</label>
                    <span class="fw-bold">{{ $dispatchOrder->created_at->format('d/m/Y') }}</span>
                </div>
                <div>
                    <label class="small text-muted d-block">Người lập:</label>
                    <span class="fw-bold">{{ $dispatchOrder->creator->name }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const dispatchMap = L.map('dispatchMap').setView([{{ $mapLat }}, {{ $mapLng }}], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(dispatchMap);
    L.marker([{{ $mapLat }}, {{ $mapLng }}]).addTo(dispatchMap)
        .bindPopup(@json($dispatchOrder->vehicle->plate_number.' - '.$dispatchOrder->driver->full_name))
        .openPopup();
</script>

<!-- Modal Thêm Chi Phí -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-navy text-white border-0 rounded-top-4 p-4">
                <h5 class="modal-title fw-bold">Ghi nhận chi phí mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf
                <input type="hidden" name="dispatch_order_id" value="{{ $dispatchOrder->id }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Loại chi phí</label>
                        <select name="expense_type" class="form-select" required>
                            <option value="Cầu đường (BOT)">Cầu đường (BOT)</option>
                            <option value="Nhiên liệu (Dầu)">Nhiên liệu (Dầu)</option>
                            <option value="Bồi dưỡng / Cafe">Bồi dưỡng / Cafe</option>
                            <option value="Lưu ca / Chờ đợi">Lưu ca / Chờ đợi</option>
                            <option value="Sửa chữa dọc đường">Sửa chữa dọc đường</option>
                            <option value="Phí bến bãi">Phí bến bãi</option>
                            <option value="Khác">Khác...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Số tiền (VNĐ)</label>
                        <input type="number" name="amount" class="form-control" placeholder="0" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="VD: Trạm thu phí Long Thành..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-navy fw-bold px-4">LƯU CHI PHÍ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tạm Ứng -->
<div class="modal fade" id="addAdvanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-navy text-white border-0 rounded-top-4 p-4">
                <h5 class="modal-title fw-bold">Yêu cầu tạm ứng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('cash-advances.store') }}" method="POST">
                @csrf
                <input type="hidden" name="dispatch_order_id" value="{{ $dispatchOrder->id }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Số tiền tạm ứng (VNĐ)</label>
                        <input type="number" name="amount" class="form-control" placeholder="VD: 500000" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted">Lý do / Nội dung chi</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="VD: Tạm ứng tiền dầu và phí cầu đường đi Hải Phòng..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-navy fw-bold px-4">GỬI YÊU CẦU</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
