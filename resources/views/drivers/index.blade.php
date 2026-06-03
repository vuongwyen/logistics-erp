@extends('layouts.app')

@section('title', 'Quản lý Tài xế')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h4 class="fw-bold mb-0">Quản lý Đội ngũ Tài xế</h4>
    
    <x-export-buttons />
</div>

<div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white">
    <form action="{{ route('drivers.index') }}" method="GET" class="row g-3">
        <!-- <div class="col-md-5">
            <input type="text" name="search" class="form-control border-light" placeholder="Tìm theo mã tài xế, tên, GPLX, cấp bậc..." value="{{ request('search') }}">
        </div> -->
        <div class="col-md-3">
            <select name="rank" class="form-select border-light">
                <option value="">Cấp bậc</option>
                @foreach(\App\Support\LogisticsOptions::driverRanks() as $value => $label)
                    <option value="{{ $value }}" {{ request('rank') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select border-light">
                <option value="">Tất cả</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang làm việc</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nghỉ việc</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-navy w-100">Lọc</button>
        </div>
        <div class="col-md-3"><input type="text" name="driver_code" class="form-control border-light" placeholder="Mã tài xế" value="{{ request('driver_code') }}"></div>
        <div class="col-md-3"><input type="text" name="full_name" class="form-control border-light" placeholder="Họ tên" value="{{ request('full_name') }}"></div>
        <div class="col-md-3"><input type="text" name="phone" class="form-control border-light" placeholder="SĐT" value="{{ request('phone') }}"></div>
        <div class="col-md-3"><input type="text" name="license_number" class="form-control border-light" placeholder="GPLX" value="{{ request('license_number') }}"></div>
    </form>
</div>

<div class="d-flex justify-content-end mb-4">
    <button class="btn btn-navy px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#driverModal" onclick="prepareAdd()">
        <i class="fa fa-plus me-2"></i> THÊM TÀI XẾ
    </button>
</div>

<!-- Data Table -->
<div class="card border-0 rounded-4 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="small text-muted text-uppercase">
                    <th class="ps-4">Mã / Họ và Tên</th>
                    <th>Số Điện Thoại</th>
                    <th>Ngày sinh</th>
                    <th>Số Bằng Lái</th>
                    <th>Cấp bậc / Hợp đồng</th>
                    <th>Trạng Thái</th>
                    <th>Ghi chú</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($drivers as $driver)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-navy">{{ $driver->driver_code ?? '---' }}</div>
                            <div class="small">{{ $driver->full_name }}</div>
                            <div class="small text-muted">Vào làm: {{ $driver->start_date?->format('d/m/Y') ?? '---' }}</div>
                        </td>
                        <td>{{ $driver->phone }}</td>
                        <td>{{ $driver->date_of_birth?->format('d/m/Y') ?? '---' }}</td>
                        <td><code>{{ $driver->license_number }}</code></td>
                        <td>
                            <div class="fw-bold">{{ $driver->rank ?: '---' }}</div>
                            <div class="small text-muted">HĐ đến: {{ $driver->contract_expiry?->format('d/m/Y') ?? '---' }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $driver->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                {{ $driver->status === 'active' ? 'Đang làm việc' : 'Nghỉ việc' }}
                            </span>
                        </td>
                        <td class="small text-muted" style="max-width: 180px;">{{ $driver->note ?: '---' }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm text-warning me-2" 
                                onclick="prepareEdit({{ json_encode($driver) }})"
                                data-bs-toggle="modal" data-bs-target="#driverModal">
                                <i class="fa fa-edit"></i>
                            </button>
                            <a href="javascript:void(0)" class="text-danger" title="Xóa" onclick="handleDelete('{{ $driver->id }}', 'Xóa tài xế {{ $driver->full_name }}?')">
                                <i class="fa fa-trash"></i>
                            </a>
                            <form id="delete-form-{{ $driver->id }}" action="{{ route('drivers.destroy', $driver->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">Chưa có dữ liệu tài xế.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-top">
        {{ $drivers->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- Driver Modal -->
<div class="modal fade" id="driverModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="driverForm" method="POST">
                @csrf
                <div id="methodField"></div>
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title fw-bold" id="modalTitle">Thêm Tài Xế Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Họ và Tên</label>
                            <input type="text" name="full_name" id="full_name" class="form-control bg-light border-0" required oninput="formatName(this)">
                            <div class="invalid-feedback" id="full_name_error" style="display: none;"></div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Số Điện Thoại</label>
                            <input type="text" name="phone" id="phone" class="form-control bg-light border-0" maxlength="10" inputmode="numeric" required oninput="formatPhone(this)">
                            <div class="invalid-feedback" id="phone_error" style="display: none;"></div>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Ngày sinh</label>
                            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Số Bằng Lái</label>
                            <input type="text" name="license_number" id="license_number" class="form-control bg-light border-0" maxlength="12" required oninput="formatLicense(this)">
                            <div class="invalid-feedback" id="license_number_error" style="display: none;"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ngày bắt đầu làm việc</label>
                            <input type="date" name="start_date" id="start_date" class="form-control bg-light border-0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cấp bậc</label>
                            <select name="rank" id="rank" class="form-select bg-light border-0">
                                <option value="">Chọn cấp bậc</option>
                                @foreach(\App\Support\LogisticsOptions::driverRanks() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Thời hạn hợp đồng</label>
                            <input type="date" name="contract_expiry" id="contract_expiry" class="form-control bg-light border-0">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Trạng Thái</label>
                            <select name="status" id="status" class="form-select bg-light border-0" required>
                                <option value="active">Đang làm việc</option>
                                <option value="inactive">Nghỉ việc</option>
                            </select>
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
        document.getElementById('modalTitle').innerText = 'Thêm Tài Xế Mới';
        document.getElementById('driverForm').action = "{{ route('drivers.store') }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('driverForm').reset();
        
        let today = new Date();
        let adultDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
        
        if (window.setDateValue) {
            setDateValue('date_of_birth', adultDate);
            setDateValue('start_date', today);
            setDateValue('contract_expiry', today);
        }
    }

    function prepareEdit(driver) {
        document.getElementById('modalTitle').innerText = 'Chỉnh Sửa Thông Tin Tài Xế';
        document.getElementById('driverForm').action = `/drivers/${driver.id}`;
        document.getElementById('methodField').innerHTML = '@method("PUT")';
        
        document.getElementById('full_name').value = driver.full_name;
        document.getElementById('phone').value = driver.phone;
        document.getElementById('date_of_birth').value = driver.date_of_birth ? driver.date_of_birth.split('T')[0].split(' ')[0] : '';
        document.getElementById('license_number').value = driver.license_number;
        document.getElementById('status').value = driver.status;
        document.getElementById('start_date').value = driver.start_date ? driver.start_date.split('T')[0].split(' ')[0] : '';
        document.getElementById('rank').value = driver.rank || '';
        document.getElementById('contract_expiry').value = driver.contract_expiry ? driver.contract_expiry.split('T')[0].split(' ')[0] : '';
        document.getElementById('note').value = driver.note || '';
    }
</script>
@endpush
@endsection
