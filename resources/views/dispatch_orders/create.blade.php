@extends('layouts.app')

@section('title', 'Lập Lệnh Điều Xe')

@section('content')
<div class="mb-4">
    <a href="{{ route('shipping-jobs.show', $shippingJob->id) }}" class="text-navy text-decoration-none small fw-bold">
        <i class="fa fa-arrow-left me-1"></i> Quay lại đơn hàng
    </a>
    <h4 class="fw-bold mt-2">Lập Lệnh Điều Xe Cho Job: {{ $shippingJob->job_code }}</h4>
</div>

<div class="row g-4">
    <!-- Job Summary -->
    <div class="col-lg-4">
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-header bg-navy text-white p-4 rounded-top-4">
                <h6 class="mb-0 fw-bold"><i class="fa fa-info-circle me-2"></i> Thông tin đơn hàng</h6>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="small text-muted d-block">Khách hàng:</label>
                    <span class="fw-bold text-navy">{{ $shippingJob->customer->customer_name }}</span>
                </div>
                <div class="mb-3">
                    <label class="small text-muted d-block">Lộ trình:</label>
                    <div class="small">
                        <span class="badge bg-light text-dark border">{{ $shippingJob->pickupLocation->location_name }}</span>
                        <i class="fa fa-arrow-right mx-1 text-muted"></i>
                        <span class="badge bg-light text-dark border">{{ $shippingJob->deliveryLocation->location_name }}</span>
                    </div>
                </div>
                <div class="mb-0">
                    <label class="small text-muted d-block">Hàng hóa:</label>
                    <span class="fw-bold">{{ $shippingJob->cargo_type }}</span>
                    <div class="small text-muted">Cont: {{ $shippingJob->container_number ?? 'N/A' }} / {{ $shippingJob->container_type ?? 'Hàng lẻ' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dispatch Form -->
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm p-4">
            <form action="{{ route('dispatch-orders.store') }}" method="POST">
                @csrf
                <input type="hidden" name="shipping_job_id" value="{{ $shippingJob->id }}">

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy">Chọn Xe (Đầu kéo/Tải) <span class="text-danger">*</span></label>
                        <select name="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror" required>
                            <option value="">-- Chọn xe --</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                    {{ $vehicle->plate_number }} ({{ $vehicle->vehicle_type }} - {{ $vehicle->payload }} tấn)
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy">Chọn Tài Xế <span class="text-danger">*</span></label>
                        <select name="driver_id" class="form-select @error('driver_id') is-invalid @enderror" required>
                            <option value="">-- Chọn tài xế --</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}" {{ old('driver_id') == $driver->id ? 'selected' : '' }}>
                                    {{ $driver->full_name }} ({{ $driver->license_number }})
                                </option>
                            @endforeach
                        </select>
                        @error('driver_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy">Chọn Mooc/Rơ-mooc</label>
                        <select name="trailer_id" class="form-select @error('trailer_id') is-invalid @enderror">
                            <option value="">-- Không chọn --</option>
                            @foreach($trailers as $trailer)
                                <option value="{{ $trailer->id }}" {{ old('trailer_id') == $trailer->id ? 'selected' : '' }}>
                                    {{ $trailer->plate_number }} ({{ $trailer->vehicle_type }})
                                </option>
                            @endforeach
                        </select>
                        @error('trailer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy">Địa điểm bắt đầu</label>
                        <select name="start_location_id" class="form-select">
                            <option value="">Theo điểm bốc của đơn hàng</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ old('start_location_id', $shippingJob->pickup_location_id) == $location->id ? 'selected' : '' }}>
                                    {{ $location->location_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy">Địa điểm kết thúc</label>
                        <select name="end_location_id" class="form-select">
                            <option value="">Theo điểm dỡ của đơn hàng</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ old('end_location_id', $shippingJob->delivery_location_id) == $location->id ? 'selected' : '' }}>
                                    {{ $location->location_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy">Định mức Nhiên liệu (Lít)</label>
                        <div class="input-group">
                            <input type="number" step="0.1" name="fuel_quota" class="form-control" placeholder="0.0">
                            <span class="input-group-text bg-light border-start-0">Lít</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy">Định mức giá dầu (VNĐ/Lít)</label>
                        <div class="input-group">
                            <input type="number" name="fuel_price_quota" class="form-control" placeholder="0">
                            <span class="input-group-text bg-light border-start-0">VNĐ/Lít</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy">Định mức Cầu đường (VNĐ)</label>
                        <div class="input-group">
                            <input type="number" name="toll_quota" class="form-control" placeholder="0">
                            <span class="input-group-text bg-light border-start-0">VNĐ</span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy">Ngày đi <span class="text-danger">*</span></label>
                        <input type="text" name="planned_departure_date" class="form-control @error('planned_departure_date') is-invalid @enderror" value="{{ \App\Support\VietnameseDate::display(old('planned_departure_date', now()->toDateString())) }}" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Ngày đi" required>
                        @error('planned_departure_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold text-navy">Ngày về <span class="text-danger">*</span></label>
                        <input type="text" name="planned_return_date" class="form-control @error('planned_return_date') is-invalid @enderror" value="{{ \App\Support\VietnameseDate::display(old('planned_return_date', now()->addDay()->toDateString())) }}" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Ngày về" required>
                        @error('planned_return_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold text-navy">Ghi chú điều vận</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Nhập hướng dẫn cho tài xế..."></textarea>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold text-navy">Loading ban đầu (%)</label>
                        <input type="number" name="loading_percent" class="form-control" min="0" max="100" value="{{ old('loading_percent', 0) }}">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-bold text-navy">Vị trí hiện tại</label>
                        <input type="hidden" name="current_latitude">
                        <input type="hidden" name="current_longitude">
                        <button type="button" class="btn btn-outline-navy w-100" data-current-location>
                            <i class="fa fa-location-crosshairs me-2"></i> Cập nhật vị trí hiện tại
                        </button>
                        <div class="small text-muted mt-1" data-location-status>Người dùng sẽ được hỏi quyền chia sẻ vị trí.</div>
                    </div>

                    <div class="col-12 mt-5">
                        <button type="submit" class="btn btn-navy px-5 py-2 fw-bold">GỬI KẾ TOÁN DUYỆT</button>
                        <a href="{{ route('shipping-jobs.show', $shippingJob->id) }}" class="btn btn-light px-5 py-2 fw-bold ms-2">HỦY</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
