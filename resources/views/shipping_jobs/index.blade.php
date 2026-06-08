@extends('layouts.app')

@section('title', 'Quản lý Đơn hàng')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h4 class="fw-bold mb-0">Danh sách Đơn hàng (Jobs)</h4>
    <x-export-buttons />
</div>

<!-- Filters -->
<div class="card border-0 rounded-4 shadow-sm p-4 mb-4">
    <form action="{{ route('shipping-jobs.index') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-lg-2 col-md-4">
            <label class="form-label small fw-bold text-muted">Mã đơn hàng</label>
            <input type="text" name="job_code" class="form-control border-light" placeholder="Mã Job" value="{{ request('job_code') }}">
        </div>
        <div class="col-lg-2 col-md-6">
            <label class="form-label small fw-bold text-muted">Khách hàng</label>
            <select name="customer_id" class="form-select border-light">
                <option value="">Tất cả khách hàng</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ (string) request('customer_id') === (string) $customer->id ? 'selected' : '' }}>
                        {{ $customer->customer_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label small fw-bold text-muted">Trạng thái đơn hàng</label>
            <select name="status" class="form-select border-light">
                <option value="">Tất cả trạng thái</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>Mới tạo</option>
                <option value="dispatched" {{ request('status') == 'dispatched' ? 'selected' : '' }}>Đã điều xe</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
            </select>
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label small fw-bold text-muted">Hạn xử lý từ ngày</label>
            <input type="text" name="date_from" class="form-control border-light" value="{{ \App\Support\VietnameseDate::display(request('date_from')) }}" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Hạn xử lý từ ngày">
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label small fw-bold text-muted">Hạn xử lý đến ngày</label>
            <input type="text" name="date_to" class="form-control border-light" value="{{ \App\Support\VietnameseDate::display(request('date_to')) }}" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Hạn xử lý đến ngày">
        </div>
        <div class="col-lg-2 col-md-4">
            <label class="form-label small fw-bold text-muted">Ngày tạo đơn</label>
            <input type="text" name="created_date" class="form-control border-light" value="{{ \App\Support\VietnameseDate::display(request('created_date')) }}" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Ngày tạo đơn">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Số container</label>
            <input type="text" name="container_number" class="form-control border-light" placeholder="TCNU1234567" value="{{ request('container_number') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Tờ khai hải quan</label>
            <input type="text" name="customs_declaration_no" class="form-control border-light" placeholder="12 chữ số" value="{{ request('customs_declaration_no') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Loại hàng hóa</label>
            <input type="text" name="cargo_type" class="form-control border-light" placeholder="Hàng hóa" value="{{ request('cargo_type') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Loại container</label>
            <input type="text" name="container_type" class="form-control border-light" placeholder="Loại cont" value="{{ request('container_type') }}">
        </div>
        <div class="col-md-2 ms-md-auto">
            <button type="submit" class="btn btn-navy w-100">Lọc</button>
        </div>
        <div class="col-md-2">
            <a href="{{ route('shipping-jobs.index') }}" class="btn btn-light w-100">Xóa lọc</a>
        </div>
    </form>
</div>

<!-- Data Table -->
@if(Auth::user()->hasRole(['ADMIN', 'SALES']))
<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('shipping-jobs.create') }}" class="btn btn-navy px-4 fw-bold">
        <i class="fa fa-plus me-2"></i> TẠO ĐƠN HÀNG MỚI
    </a>
</div>
@endif
<div class="card border-0 rounded-4 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="small text-muted text-uppercase">
                    <th class="ps-4">Mã Job / Ngày tạo</th>
                    <th>Khách Hàng</th>
                    <th>Hạn xử lý</th>
                    <th>Tuyến Đường (Bốc -> Dỡ)</th>
                    <th>Container / Loại hàng</th>
                    <th>Hồ sơ</th>
                    <th>Trạng Thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shippingJobs as $job)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-navy">{{ $job->job_code }}</div>
                            <div class="small text-muted">{{ $job->created_at->format('d/m/Y') }}</div>
                        </td>
                        <td>
                            <div class="fw-bold text-truncate" style="max-width: 200px;">{{ $job->customer->customer_name }}</div>
                            <div class="small text-muted">{{ $job->customer->customer_code }}</div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ $job->expected_date ? \Carbon\Carbon::parse($job->expected_date)->format('d/m/Y') : '---' }}</div>
                            <div class="small {{ $job->expected_date && \Carbon\Carbon::parse($job->expected_date)->isPast() && $job->status !== 'completed' ? 'text-danger fw-bold' : 'text-muted' }}">
                                {{ $job->expected_date ? \Carbon\Carbon::parse($job->expected_date)->diffForHumans() : '' }}
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <span class="badge bg-light text-dark fw-normal border">{{ $job->pickupLocation->location_name }}</span>
                                <i class="fa fa-arrow-right mx-1 text-muted small"></i>
                                <span class="badge bg-light text-dark fw-normal border">{{ $job->deliveryLocation->location_name }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="fw-bold">{{ filled($job->container_number) ? $job->container_number : 'Hàng lẻ' }}</div>
                            <div class="small text-muted">{{ $job->cargo_type }} ({{ $job->container_type ?? 'Lẻ' }})</div>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-1 small">
                                <span><i class="fa fa-route text-primary me-1"></i>{{ $job->dispatch_orders_count }} lệnh</span>
                                <span><i class="fa fa-folder text-warning me-1"></i>{{ $job->documents_count }} chứng từ</span>
                                <span><i class="fa fa-receipt text-success me-1"></i>{{ $job->expenses_count }} chi phí</span>
                            </div>
                        </td>
                        <td>
                            @php
                                $statusClass = match($job->status) {
                                    'new' => 'bg-info text-dark',
                                    'processing' => 'bg-primary',
                                    'dispatched' => 'bg-warning text-dark',
                                    'completed' => 'bg-success',
                                    'cancelled' => 'bg-secondary',
                                    default => 'bg-light text-dark'
                                };
                                $statusName = match($job->status) {
                                    'new' => 'Mới tạo',
                                    'processing' => 'Đang xử lý',
                                    'dispatched' => 'Đã điều xe',
                                    'completed' => 'Hoàn thành',
                                    'cancelled' => 'Đã hủy',
                                    default => 'Khác'
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $statusName }}</span>
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                    <li><a class="dropdown-item" href="{{ route('shipping-jobs.show', $job->id) }}"><i class="fa fa-eye me-2 text-info"></i> Chi tiết</a></li>
                                    @if($job->dispatch_orders_count === 0 && Auth::user()->hasRole(['ADMIN', 'DISPATCH']))
                                        <li><a class="dropdown-item" href="{{ route('dispatch-orders.create', ['shipping_job_id' => $job->id]) }}"><i class="fa fa-truck me-2 text-success"></i> Lập lệnh điều xe</a></li>
                                    @endif
                                    @if(Auth::user()->hasRole(['ADMIN', 'SALES']))
                                    <li><a class="dropdown-item" href="{{ route('shipping-jobs.edit', $job->id) }}"><i class="fa fa-edit me-2 text-warning"></i> Chỉnh sửa</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="handleDelete('{{ $job->id }}', 'Xóa đơn hàng {{ $job->job_code }}?')">
                                            <i class="fa fa-trash me-2"></i> Xóa
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                            <form id="delete-form-{{ $job->id }}" action="{{ route('shipping-jobs.destroy', $job->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">Không tìm thấy đơn hàng nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-top">
        {{ $shippingJobs->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
