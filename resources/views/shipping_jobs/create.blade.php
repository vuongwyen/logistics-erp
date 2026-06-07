@extends('layouts.app')

@section('title', 'Tạo Đơn Hàng Mới')

@section('content')
<div class="mb-4">
    <a href="{{ route('shipping-jobs.index') }}" class="text-navy text-decoration-none small fw-bold">
        <i class="fa fa-arrow-left me-1"></i> Quay lại danh sách
    </a>
    <h4 class="fw-bold mt-2">Tạo Đơn Hàng Vận Chuyển Mới</h4>
</div>

<div class="card border-0 rounded-4 shadow-sm p-4">
    <form action="{{ route('shipping-jobs.store') }}" method="POST">
        @csrf
        
        <div class="row g-4">
            <!-- Customer Section -->
            <div class="col-md-6">
                <label class="form-label fw-bold text-navy">Khách Hàng <span class="text-danger">*</span></label>
                <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                    <option value="">-- Chọn khách hàng --</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
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
                <input type="date" name="expected_date" class="form-control @error('expected_date') is-invalid @enderror" value="{{ old('expected_date', date('Y-m-d')) }}" required>
                @error('expected_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Route Section -->
            <div class="col-md-6">
                <label class="form-label fw-bold text-navy">Địa Điểm Bốc Hàng <span class="text-danger">*</span></label>
                <select name="pickup_location_id" class="form-select @error('pickup_location_id') is-invalid @enderror" required>
                    <option value="">-- Chọn nơi bốc --</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ old('pickup_location_id') == $location->id ? 'selected' : '' }}>
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
                    <option value="">-- Chọn nơi dỡ --</option>
                    @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ old('delivery_location_id') == $location->id ? 'selected' : '' }}>
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
                <input type="text" name="cargo_type" class="form-control @error('cargo_type') is-invalid @enderror" value="{{ old('cargo_type') }}" placeholder="VD: Gạo, Gỗ, Điện tử..." required>
                @error('cargo_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold text-navy">Loại Container</label>
                <select name="container_type" class="form-select">
                    <option value="">-- Chọn loại (nếu có) --</option>
                    <option value="20DC" {{ old('container_type') == '20DC' ? 'selected' : '' }}>20 DC</option>
                    <option value="40DC" {{ old('container_type') == '40DC' ? 'selected' : '' }}>40 DC</option>
                    <option value="45HC" {{ old('container_type') == '45HC' ? 'selected' : '' }}>45 HC</option>
                    <option value="Flatrack" {{ old('container_type') == 'Flatrack' ? 'selected' : '' }}>Flatrack</option>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold text-navy">Gói dịch vụ <span class="text-danger">*</span></label>
                <select name="service_price_id" class="form-select @error('service_price_id') is-invalid @enderror" required>
                    <option value="">-- Chọn Gói dịch vụ --</option>
                    @foreach($servicePrices as $service)
                        <option value="{{ $service->id }}" {{ old('service_price_id') == $service->id ? 'selected' : '' }}>
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
                <input type="text" name="container_number" class="form-control @error('container_number') is-invalid @enderror" value="{{ old('container_number') }}" placeholder="VD: TCNU1234567" maxlength="11" pattern="[A-Z]{4}[0-9]{7}" title="Số container phải đúng chuẩn ISO 6346: 4 chữ cái theo sau là 7 chữ số (VD: TCNU1234567)" oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');">
                @error('container_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-12">
                <label class="form-label fw-bold text-navy">Số Tờ Khai Hải Quan (nếu có)</label>
                <input type="text" name="customs_declaration_no" class="form-control" value="{{ old('customs_declaration_no') }}" placeholder="VD: 104523456789">
            </div>

            <div class="col-12 mt-5">
                <button type="submit" class="btn btn-navy px-5 py-2 fw-bold">LƯU ĐƠN HÀNG</button>
                <a href="{{ route('shipping-jobs.index') }}" class="btn btn-light px-5 py-2 fw-bold ms-2">HỦY</a>
            </div>
        </div>
    </form>
</div>
@endsection
