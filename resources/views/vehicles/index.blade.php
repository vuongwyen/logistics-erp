@extends('layouts.app')

@section('title', 'Quản lý Đội xe')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h4 class="fw-bold mb-0">Quản lý Đội xe</h4>
    
    <x-export-buttons />
</div>

<div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white">
    <form action="{{ route('vehicles.index') }}" method="GET" class="row g-3">
        <!-- <div class="col-md-7">
            <input type="text" name="search" class="form-control border-light" placeholder="Tìm theo biển số, loại xe..." value="{{ request('search') }}">
        </div> -->
        <div class="col-md-3">
            <select name="status" class="form-select border-light">
                <option value="">Tất cả trạng thái</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Sẵn sàng</option>
                <option value="busy" {{ request('status') === 'busy' ? 'selected' : '' }}>Đang chạy</option>
                <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Bảo trì</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-navy w-100">Lọc</button>
        </div>
        <div class="col-md-3"><input type="text" name="plate_number" class="form-control border-light" placeholder="Biển số" value="{{ request('plate_number') }}"></div>
        <div class="col-md-3">
            <select name="vehicle_type" class="form-select border-light">
                <option value="">Loại xe</option>
                @foreach(\App\Support\LogisticsOptions::vehicleTypes() as $value => $label)
                    <option value="{{ $value }}" {{ request('vehicle_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="payload" class="form-select border-light">
                <option value="">Tải trọng</option>
                @foreach(\App\Support\LogisticsOptions::payloads() as $value => $label)
                    <option value="{{ $value }}" {{ request('payload') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </form>
</div>

<div class="d-flex justify-content-end mb-4">
    <button class="btn btn-navy px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#vehicleModal" onclick="prepareAdd()">
        <i class="fa fa-plus me-2"></i> THÊM XE MỚI
    </button>
</div>

<!-- Data Table -->
<div class="card border-0 rounded-4 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="small text-muted text-uppercase">
                    <th class="ps-4">Biển Số Xe</th>
                    <th>Loại Xe / Tải Trọng</th>
                    <th>Hạn Đăng Kiểm</th>
                    <th>Trạng Thái</th>
                    <th>Ghi chú</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $vehicle)
                    <tr>
                        <td class="ps-4 fw-bold text-navy">{{ $vehicle->plate_number }}</td>
                        <td>
                            <div class="fw-bold">{{ $vehicle->vehicle_type }}</div>
                            <div class="small text-muted">{{ number_format($vehicle->payload, 1) }} Tấn</div>
                        </td>
                        <td>
                            @if($vehicle->registration_expiry)
                                @php
                                    $expiry = \Carbon\Carbon::parse($vehicle->registration_expiry);
                                    $isExpired = $expiry->isPast();
                                    $isNear = !$isExpired && $expiry->diffInDays(now()) <= 30;
                                    $colorClass = $isExpired ? 'text-danger fw-bold' : ($isNear ? 'text-warning fw-bold' : '');
                                @endphp
                                <span class="{{ $colorClass }}">
                                    <i class="fa fa-calendar-day me-1"></i>
                                    {{ $expiry->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-muted small">Chưa cập nhật</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $statusClass = match($vehicle->status) {
                                    'available' => 'bg-success',
                                    'busy' => 'bg-warning text-dark',
                                    'maintenance' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                                $statusName = match($vehicle->status) {
                                    'available' => 'Sẵn sàng',
                                    'busy' => 'Đang chạy',
                                    'maintenance' => 'Bảo trì',
                                    default => 'Khác'
                                };
                            @endphp
                            <span class="badge {{ $statusClass }}">{{ $statusName }}</span>
                        </td>
                        <td class="small text-muted" style="max-width: 220px;">{{ $vehicle->note ?: '---' }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm text-warning me-2" 
                                onclick="prepareEdit({{ json_encode($vehicle) }})"
                                data-bs-toggle="modal" data-bs-target="#vehicleModal">
                                <i class="fa fa-edit"></i>
                            </button>
                            <a href="javascript:void(0)" class="text-danger" title="Xóa" onclick="handleDelete('{{ $vehicle->id }}', 'Xóa xe {{ $vehicle->plate_number }}?')">
                                <i class="fa fa-trash"></i>
                            </a>
                            <form id="delete-form-{{ $vehicle->id }}" action="{{ route('vehicles.destroy', $vehicle->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Chưa có dữ liệu xe.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-top">
        {{ $vehicles->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- Vehicle Modal -->
<div class="modal fade" id="vehicleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="vehicleForm" method="POST">
                @csrf
                <div id="methodField"></div>
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title fw-bold" id="modalTitle">Thêm Xe Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Biển Số Xe</label>
                            <input type="text" name="plate_number" id="plate_number" class="form-control bg-light border-0 @error('plate_number') is-invalid @enderror" placeholder="VD: 51C-123.45" required oninput="formatLicensePlate(this)">
                            <div class="invalid-feedback" id="plate_number_error" style="display: none;"></div>
                            @error('plate_number')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Loại Xe</label>
                            <select name="vehicle_type" id="vehicle_type" class="form-select bg-light border-0" required>
                                @foreach(\App\Support\LogisticsOptions::vehicleTypes() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tải Trọng (Tấn)</label>
                            <select name="payload" id="payload" class="form-select bg-light border-0" required>
                                @foreach(\App\Support\LogisticsOptions::payloads() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Trạng Thái</label>
                            <select name="status" id="status" class="form-select bg-light border-0" required>
                                <option value="available">Sẵn sàng</option>
                                <option value="busy">Đang chạy</option>
                                <option value="maintenance">Bảo trì</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Hạn Đăng Kiểm</label>
                            <input type="date" name="registration_expiry" id="registration_expiry" class="form-control bg-light border-0">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Ghi chú</label>
                            <textarea name="note" id="note" class="form-control bg-light border-0" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-navy fw-bold px-4">Lưu Thông Tin</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function prepareAdd() {
        document.getElementById('modalTitle').innerText = 'Thêm Xe Mới';
        document.getElementById('vehicleForm').action = "{{ route('vehicles.store') }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('vehicleForm').reset();
    }

    function prepareEdit(vehicle) {
        document.getElementById('modalTitle').innerText = 'Chỉnh Sửa Thông Tin Xe';
        document.getElementById('vehicleForm').action = `/vehicles/${vehicle.id}`;
        document.getElementById('methodField').innerHTML = '@method("PUT")';
        
        document.getElementById('plate_number').value = vehicle.plate_number;
        document.getElementById('vehicle_type').value = vehicle.vehicle_type;
        document.getElementById('payload').value = vehicle.payload;
        document.getElementById('status').value = vehicle.status;
        document.getElementById('registration_expiry').value = vehicle.registration_expiry ? vehicle.registration_expiry.split(' ')[0] : '';
        document.getElementById('note').value = vehicle.note || '';
    }

    function formatLicensePlate(input) {
        let val = input.value.toUpperCase();
        let errorMsg = '';
        
        // Remove spaces and any invalid chars overall to keep it clean
        val = val.replace(/[^A-Z0-9\-\.]/g, '');

        if (val.length > 0) {
            let part1 = val.split('-')[0] || '';
            let part2 = val.split('-')[1] || '';

            // Kiểm tra mã vùng (2 ký tự đầu phải là số)
            if (part1.length >= 2 && !/^[0-9]{2}/.test(part1)) {
                errorMsg = "2 ký tự đầu phải là mã vùng (số).";
            }
            // Kiểm tra series (sau mã vùng phải là chữ)
            else if (part1.length > 2 && !/^[0-9]{2}[A-Z]{1,2}$/.test(part1)) {
                errorMsg = "Sau mã vùng phải là 1 hoặc 2 chữ cái series.";
            }
            // Nếu đã gõ xong phần 1 và chưa có dấu -
            else if (part1.length >= 3 && part1.length <= 4 && !val.includes('-') && input.value.length > part1.length) {
                // Tự động thêm - (optional) or let user type
                // But user rules: bắt lỗi.
            }
            // Kiểm tra phần 2
            else if (val.includes('-')) {
                if (part2.length > 0) {
                    let p2_1 = part2.split('.')[0] || '';
                    let p2_2 = part2.split('.')[1] || '';

                    if (p2_1.length > 0 && !/^[0-9]+$/.test(p2_1)) {
                        errorMsg = "Sau dấu '-' phải là số.";
                    } else if (p2_1.length > 3) {
                        errorMsg = "Sau dấu '-' chỉ được 3 chữ số, sau đó là dấu chấm.";
                    }

                    if (part2.includes('.')) {
                        if (p2_2.length > 0 && !/^[0-9]+$/.test(p2_2)) {
                            errorMsg = "Sau dấu '.' phải là 2 chữ số.";
                        } else if (p2_2.length > 2) {
                            errorMsg = "Chỉ được nhập 2 số sau dấu '.'.";
                        }
                    }
                }
            }
            
            // Lỗi tổng thể (Regex cuối)
            if (val.length >= 8 && !errorMsg) {
                if (!/^[0-9]{2}[A-Z]{1,2}-[0-9]{3}\.[0-9]{2}$/.test(val)) {
                    errorMsg = "Định dạng biển số chưa đúng chuẩn (VD: 51C-123.45, 51R-123.45).";
                }
            }
        }

        input.value = val;

        let errorDiv = document.getElementById('plate_number_error');
        if (errorMsg) {
            input.classList.add('is-invalid');
            input.setCustomValidity(errorMsg);
            if (errorDiv) {
                errorDiv.innerText = errorMsg;
                errorDiv.style.display = 'block';
                errorDiv.classList.add('d-block');
            }
        } else {
            input.classList.remove('is-invalid');
            input.setCustomValidity('');
            if (errorDiv) {
                errorDiv.style.display = 'none';
                errorDiv.classList.remove('d-block');
            }
        }
    }
</script>
@endpush
@endsection
