@extends('layouts.app')

@section('title', 'Quản lý Điều vận')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h4 class="fw-bold mb-0">Danh sách Lệnh điều xe</h4>
    <div class="d-flex gap-2">
        <button class="btn btn-navy px-4 fw-bold">
            <i class="fa fa-print me-2"></i> IN DANH SÁCH
        </button>
        <x-export-buttons />
    </div>
</div>

@if(Auth::user()->hasRole(['ADMIN', 'DISPATCH']))
<!-- Pending Jobs for Dispatching -->
<div class="card border-0 rounded-4 shadow-sm mb-4">
    <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold text-navy mb-0"><i class="fa fa-clock me-2"></i> Đơn hàng chờ phân công</h5>
        <span class="badge bg-danger rounded-pill">{{ count($pendingJobs) }} đơn hàng</span>
    </div>
    <div class="card-body p-4">
        @if(count($pendingJobs) > 0)
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-muted text-uppercase">
                        <th>Mã Job / Khách hàng</th>
                        <th>Lộ trình (Tuyến)</th>
                        <th>Hàng hóa</th>
                        <th class="text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingJobs as $job)
                    <tr>
                        <td>
                            <div class="fw-bold text-navy">{{ $job->job_code }}</div>
                            <div class="small text-muted">{{ $job->customer->customer_name }}</div>
                        </td>
                        <td>
                            <div class="small fw-bold">{{ $job->pickupLocation->location_name }}</div>
                            <div class="small text-muted"><i class="fa fa-arrow-right mx-1"></i> {{ $job->deliveryLocation->location_name }}</div>
                        </td>
                        <td>
                            <div class="small text-muted">{{ $job->cargo_type }}</div>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('dispatch-orders.create', ['shipping_job_id' => $job->id]) }}" class="btn btn-sm btn-navy px-3 fw-bold">
                                <i class="fa fa-plus-circle me-1"></i> LẬP LỆNH
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-3 text-muted small">
            Tất cả đơn hàng đã được phân công.
        </div>
        @endif
    </div>
</div>
@endif

<!-- Filters -->
<div class="card border-0 rounded-4 shadow-sm p-4 mb-4">
    <form action="{{ route('dispatch-orders.index') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Số lệnh</label>
            <input type="text" name="order_number" class="form-control border-light" placeholder="Số lệnh" value="{{ request('order_number') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Mã đơn hàng</label>
            <input type="text" name="job_code" class="form-control border-light" placeholder="Mã Job" value="{{ request('job_code') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Tài xế</label>
            <input type="text" name="driver_name" class="form-control border-light" placeholder="Tài xế" value="{{ request('driver_name') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Biển số xe</label>
            <input type="text" name="plate_number" class="form-control border-light" placeholder="Biển số" value="{{ request('plate_number') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Trạng thái duyệt</label>
            <select name="approval_status" class="form-select border-light">
                <option value="">Duyệt</option>
                <option value="pending" {{ request('approval_status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                <option value="approved" {{ request('approval_status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                <option value="rejected" {{ request('approval_status') === 'rejected' ? 'selected' : '' }}>Từ chối</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Trạng thái điều vận</label>
            <select name="dispatch_status" class="form-select border-light">
                <option value="">Tất cả chuyến</option>
                <option value="dispatched" {{ request('dispatch_status') === 'dispatched' ? 'selected' : '' }}>Đã điều xe</option>
                <option value="on_way" {{ request('dispatch_status') === 'on_way' ? 'selected' : '' }}>Đang đi</option>
                <option value="completed" {{ request('dispatch_status') === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Ngày đi</label>
            <input type="text" name="planned_departure_date" class="form-control border-light" placeholder="Ngày/Tháng/Năm" value="{{ \App\Support\VietnameseDate::display(request('planned_departure_date')) }}" data-date-input data-label="Ngày đi">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Ngày về</label>
            <input type="text" name="planned_return_date" class="form-control border-light" placeholder="Ngày/Tháng/Năm" value="{{ \App\Support\VietnameseDate::display(request('planned_return_date')) }}" data-date-input data-label="Ngày về">
        </div>
        <div class="col-md-2 ms-md-auto">
            <button type="submit" class="btn btn-navy w-100">Lọc</button>
        </div>
    </form>
</div>

<!-- Data Table -->
<div class="card border-0 rounded-4 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="small text-muted text-uppercase">
                    <th class="ps-4">Số Lệnh / Ngày tạo</th>
                    <th>Mã Job / Khách hàng</th>
                    <th>Tài Xế / Xe</th>
                    <th>Loading</th>
                    <th>Duyệt / Trạng Thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dispatchOrders as $order)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-navy">{{ $order->order_number }}</div>
                            <div class="small text-muted">{{ $order->created_at->format('d/m/Y') }}</div>
                        </td>
                        <td>
                            <div class="fw-bold"><a href="{{ route('shipping-jobs.show', $order->shipping_job_id) }}" class="text-decoration-none text-navy">{{ $order->shippingJob->job_code }}</a></div>
                            <div class="small text-muted text-truncate" style="max-width: 150px;">{{ $order->shippingJob->customer->customer_name }}</div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $order->driver->full_name }}</div>
                            <div class="small text-muted">{{ $order->vehicle->plate_number }}</div>
                        </td>
                        <td style="min-width: 140px;">
                            @php
                                $loading = (int) ($order->loading_percent ?? 0);
                                $loadingClass = $loading >= 100 ? 'bg-success' : ($loading >= 70 ? 'bg-info' : ($loading >= 35 ? 'bg-warning' : 'bg-danger'));
                            @endphp
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar {{ $loadingClass }}" style="width: {{ $loading }}%;"></div>
                            </div>
                            <div class="small text-muted mt-1">{{ $loading }}%</div>
                        </td>
                        <td>
                            @php
                                $statusName = match(true) {
                                    $order->approval_status === 'pending' => 'Chờ duyệt',
                                    $order->approval_status === 'rejected' => 'Từ chối',
                                    $order->dispatch_status === 'on_way' => 'Đang đi',
                                    $order->dispatch_status === 'completed' => 'Hoàn thành',
                                    default => 'Đã duyệt'
                                };
                                $statusClass = match(true) {
                                    $order->approval_status === 'pending' => 'bg-warning text-dark',
                                    $order->approval_status === 'rejected' => 'bg-danger',
                                    $order->dispatch_status === 'on_way' => 'bg-info text-dark',
                                    $order->dispatch_status === 'completed' => 'bg-success',
                                    default => 'bg-primary'
                                };
                            @endphp
                            @php
                                $approvalName = match($order->approval_status) {
                                    'approved' => 'Đã duyệt',
                                    'rejected' => 'Từ chối',
                                    default => 'Chờ duyệt'
                                };
                            @endphp
                            <span class="badge bg-light text-dark border mb-1">{{ $approvalName }}</span>
                            <span class="badge {{ $statusClass }}">{{ $statusName }}</span>
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li><a class="dropdown-item" href="{{ route('dispatch-orders.show', $order->id) }}"><i class="fa fa-eye me-2 text-info"></i> Chi tiết</a></li>
                                    @if(Auth::user()->hasRole(['ADMIN', 'ACCOUNTANT']) && $order->approval_status === 'pending')
                                        <li>
                                            <form action="{{ route('dispatch-orders.approve', $order) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-success">
                                                    <i class="fa fa-check me-2"></i> Duyệt lệnh
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="{{ route('dispatch-orders.reject', $order) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="rejection_reason" value="Kế toán từ chối duyệt">
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="fa fa-ban me-2"></i> Từ chối
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                    @if(Auth::user()->hasRole(['ADMIN', 'DISPATCH']))
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="handleDelete('{{ $order->id }}', 'Hủy lệnh điều xe {{ $order->order_number }}?')">
                                            <i class="fa fa-trash me-2"></i> Hủy lệnh
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                            <form id="delete-form-{{ $order->id }}" action="{{ route('dispatch-orders.destroy', $order->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Chưa có lệnh điều xe nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-top">
        {{ $dispatchOrders->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
