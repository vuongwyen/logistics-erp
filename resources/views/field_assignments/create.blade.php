@extends('layouts.app')

@section('title', 'Tạo phiếu điều nhân viên hiện trường')

@section('content')
<div class="mb-4">
    <a href="{{ route('field-assignments.index') }}" class="text-navy text-decoration-none small fw-bold">
        <i class="fa fa-arrow-left me-1"></i> Quay lại danh sách
    </a>
    <h4 class="fw-bold mt-2 mb-1">Tạo phiếu điều nhân viên hiện trường</h4>
    <p class="text-muted small mb-0">Mã phiếu, ngày tạo và người tạo sẽ được hệ thống tự động ghi nhận.</p>
</div>

<form action="{{ route('field-assignments.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-navy">Đơn hàng liên kết <span class="text-danger">*</span></label>
                            <select name="shipping_job_id" class="form-select @error('shipping_job_id') is-invalid @enderror" required>
                                <option value="">Chọn đơn hàng</option>
                                @foreach($shippingJobs as $job)
                                    <option value="{{ $job->id }}" {{ (string) old('shipping_job_id', request('shipping_job_id')) === (string) $job->id ? 'selected' : '' }}>
                                        {{ $job->job_code }} - {{ $job->customer?->customer_name }} - Cont {{ $job->container_number ?? '---' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('shipping_job_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-navy">Nhân viên được điều <span class="text-danger">*</span></label>
                            <select name="field_staff_id" class="form-select @error('field_staff_id') is-invalid @enderror" required>
                                <option value="">Chọn nhân viên hiện trường</option>
                                @foreach($fieldStaff as $staff)
                                    <option value="{{ $staff->id }}" {{ (string) old('field_staff_id') === (string) $staff->id ? 'selected' : '' }}>
                                        {{ $staff->full_name }} - {{ $staff->phone }}
                                    </option>
                                @endforeach
                            </select>
                            @error('field_staff_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-navy">Vị trí thực hiện <span class="text-danger">*</span></label>
                            <select name="location_id" class="form-select @error('location_id') is-invalid @enderror" required>
                                <option value="">Chọn vị trí</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ (string) old('location_id') === (string) $location->id ? 'selected' : '' }}>
                                        {{ $location->location_name }} - {{ $location->province }}
                                    </option>
                                @endforeach
                            </select>
                            @error('location_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-navy">Ngày thực hiện <span class="text-danger">*</span></label>
                            <input type="text" name="assigned_date" class="form-control @error('assigned_date') is-invalid @enderror" value="{{ \App\Support\VietnameseDate::display(old('assigned_date', now()->toDateString())) }}" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Ngày thực hiện" required>
                            @error('assigned_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-navy">Nhiệm vụ cần làm <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                @foreach($tasks as $taskValue => $taskLabel)
                                    <div class="col-md-6">
                                        <label class="border rounded-3 p-3 w-100 h-100 d-flex gap-2 align-items-start">
                                            <input type="checkbox" name="tasks[]" value="{{ $taskValue }}" class="form-check-input mt-1" {{ in_array($taskValue, old('tasks', []), true) ? 'checked' : '' }}>
                                            <span>{{ $taskLabel }}</span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('tasks') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            @error('tasks.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-navy">Ghi chú</label>
                            <textarea name="note" rows="4" class="form-control @error('note') is-invalid @enderror" placeholder="Thông tin bổ sung cho nhân viên hiện trường">{{ old('note') }}</textarea>
                            @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-navy mb-3">Thông tin tự động</h6>
                    <div class="small text-muted">Mã phiếu</div>
                    <div class="fw-bold mb-3">Tự động tăng</div>
                    <div class="small text-muted">Ngày tạo</div>
                    <div class="fw-bold mb-3">{{ now()->format('d/m/Y') }}</div>
                    <div class="small text-muted">Người tạo</div>
                    <div class="fw-bold mb-4">{{ Auth::user()->name }}</div>
                    <button type="submit" class="btn btn-navy w-100 py-3 fw-bold">
                        <i class="fa fa-paper-plane me-2"></i> TẠO VÀ PHÂN CÔNG
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
