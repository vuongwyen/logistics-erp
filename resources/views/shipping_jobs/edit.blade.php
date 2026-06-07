@extends('layouts.app')

@section('title', 'Chỉnh sửa Đơn hàng ' . $shippingJob->job_code)

@section('content')
<div class="mb-4">
    <a href="{{ route('shipping-jobs.show', $shippingJob->id) }}" class="text-navy text-decoration-none small fw-bold">
        <i class="fa fa-arrow-left me-1"></i> Quay lại chi tiết
    </a>
    <h4 class="fw-bold mt-2">Chỉnh sửa Đơn hàng: {{ $shippingJob->job_code }}</h4>
</div>

<div class="card border-0 rounded-4 shadow-sm p-4">
    <form action="{{ route('shipping-jobs.update', $shippingJob->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row g-4">
            <!-- Customer Section -->
            <div class="col-md-6">
                <label class="form-label fw-bold text-navy">Khách Hàng <span class="text-danger">*</span></label>
                <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id', $shippingJob->customer_id) == $customer->id ? 'selected' : '' }}>
                            {{ $customer->customer_name }} ({{ $customer->customer_code }})
                        </option>
                    @endforeach
                </select>
                @error('customer_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold text-navy">Ngày Dự Kiến <span class="text-danger">*</span></label>
                <input type="date" name="expected_date" class="form-control @error('expected_date') is-invalid @enderror" value="{{ old('expected_date', $shippingJob->expected_date ? \Carbon\Carbon::parse($shippingJob->expected_date)->format('Y-m-d') : '') }}" required>
                @error('expected_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Route Section -->
            <div class="col-md-6">
                <label class="form-label fw-bold text-navy">Địa Điểm Bốc Hàng <span class="text-danger">*</span></label>
                <select name="pickup_location_id" class="form-select @error('pickup_location_id') is-invalid @enderror" required>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ old('pickup_location_id', $shippingJob->pickup_location_id) == $location->id ? 'selected' : '' }}>
                            {{ $location->location_name }} ({{ $location->type }})
                        </option>
                    @endforeach
                </select>
                @error('pickup_location_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold text-navy">Địa Điểm Dỡ Hàng <span class="text-danger">*</span></label>
                <select name="delivery_location_id" class="form-select @error('delivery_location_id') is-invalid @enderror" required>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ old('delivery_location_id', $shippingJob->delivery_location_id) == $location->id ? 'selected' : '' }}>
                            {{ $location->location_name }} ({{ $location->type }})
                        </option>
                    @endforeach
                </select>
                @error('delivery_location_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Cargo Section -->
            <div class="col-md-4">
                <label class="form-label fw-bold text-navy">Loại Hàng Hóa <span class="text-danger">*</span></label>
                <input type="text" name="cargo_type" class="form-control @error('cargo_type') is-invalid @enderror" value="{{ old('cargo_type', $shippingJob->cargo_type) }}" required>
                @error('cargo_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold text-navy">Loại Container</label>
                <select name="container_type" class="form-select">
                    <option value="">-- Chọn loại (nếu có) --</option>
                    <option value="20DC" {{ old('container_type', $shippingJob->container_type) == '20DC' ? 'selected' : '' }}>20 DC</option>
                    <option value="40DC" {{ old('container_type', $shippingJob->container_type) == '40DC' ? 'selected' : '' }}>40 DC</option>
                    <option value="45HC" {{ old('container_type', $shippingJob->container_type) == '45HC' ? 'selected' : '' }}>45 HC</option>
                    <option value="Flatrack" {{ old('container_type', $shippingJob->container_type) == 'Flatrack' ? 'selected' : '' }}>Flatrack</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold text-navy">Gói dịch vụ <span class="text-danger">*</span></label>
                <select name="service_price_id" class="form-select @error('service_price_id') is-invalid @enderror" required>
                    <option value="">-- Chọn Gói dịch vụ --</option>
                    @foreach($servicePrices as $service)
                        <option value="{{ $service->id }}" {{ old('service_price_id', $shippingJob->service_price_id) == $service->id ? 'selected' : '' }}>
                            {{ $service->service_name }} - {{ number_format($service->unit_price) }}đ
                        </option>
                    @endforeach
                </select>
                @error('service_price_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold text-navy">Số Container</label>
                <input type="text" name="container_number" class="form-control" value="{{ old('container_number', $shippingJob->container_number) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold text-navy">Số Tờ Khai Hải Quan</label>
                <input type="text" name="customs_declaration_no" class="form-control" value="{{ old('customs_declaration_no', $shippingJob->customs_declaration_no) }}">
            </div>

            <div class="col-md-6">
                <label class="form-label fw-bold text-navy">Trạng Thái Đơn Hàng</label>
                <select name="status" class="form-select">
                    <option value="new" {{ old('status', $shippingJob->status) == 'new' ? 'selected' : '' }}>Mới tạo</option>
                    <option value="dispatched" {{ old('status', $shippingJob->status) == 'dispatched' ? 'selected' : '' }}>Đã điều xe</option>
                    <option value="completed" {{ old('status', $shippingJob->status) == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                    <option value="cancelled" {{ old('status', $shippingJob->status) == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                </select>
            </div>

            <div class="col-12 mt-5">
                <button type="submit" class="btn btn-navy px-5 py-2 fw-bold">CẬP NHẬT ĐƠN HÀNG</button>
                <a href="{{ route('shipping-jobs.show', $shippingJob->id) }}" class="btn btn-light px-5 py-2 fw-bold ms-2">HỦY</a>
            </div>
        </div>
    </form>
</div>
@endsection
