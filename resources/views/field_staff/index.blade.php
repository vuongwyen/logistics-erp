@extends('layouts.app')

@section('title', 'Quản lý Nhân viên hiện trường')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h4 class="fw-bold mb-0">Quản lý Nhân viên hiện trường</h4>
    
    <x-export-buttons />
</div>

<div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white">
    <form action="{{ route('field-staff.index') }}" method="GET" class="row g-3">
        <!-- <div class="col-md-4">
            <input type="text" name="search" class="form-control border-light" placeholder="Tìm theo mã, tên, chứng chỉ, khu vực..." value="{{ request('search') }}">
        </div> -->
        <div class="col-md-3">
            <select name="responsible_location_id" class="form-select border-light">
                <option value="">Tất cả khu vực</option>
                @foreach($responsibleLocations as $location)
                    <option value="{{ $location->id }}" {{ (string) request('responsible_location_id') === (string) $location->id ? 'selected' : '' }}>
                        {{ $location->location_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select border-light">
                <option value="">Tất cả trạng thái</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang làm việc</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nghỉ việc</option>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-navy w-100">Lọc</button>
        </div>
        <div class="col-md-3"><input type="text" name="staff_code" class="form-control border-light" placeholder="Mã" value="{{ request('staff_code') }}"></div>
        <div class="col-md-3"><input type="text" name="full_name" class="form-control border-light" placeholder="Họ tên" value="{{ request('full_name') }}"></div>
        <div class="col-md-3"><input type="text" name="phone" class="form-control border-light" placeholder="SĐT" value="{{ request('phone') }}"></div>
    </form>
</div>

<div class="d-flex justify-content-end mb-4">
    <button class="btn btn-navy px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#fieldStaffModal" onclick="prepareAdd()">
        <i class="fa fa-plus me-2"></i> THÊM NHÂN VIÊN
    </button>
</div>

<div class="card border-0 rounded-4 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="small text-muted text-uppercase">
                    <th class="ps-4">Mã / Họ và tên</th>
                    <th>SĐT / Ngày sinh</th>
                    <th>Khu vực phụ trách</th>
                    <th>Chứng chỉ</th>
                    <th>Trạng thái</th>
                    <th>Ghi chú</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fieldStaff as $staff)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-navy">{{ $staff->staff_code }}</div>
                            <div class="small">{{ $staff->full_name }}</div>
                            <div class="small text-muted">
                                {{ $staff->phone ?: 'Chưa có số điện thoại' }}
                                <span class="mx-1">•</span>
                                Vào làm: {{ $staff->start_date?->format('d/m/Y') ?? '---' }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $staff->phone ?: '---' }}</div>
                            <div class="small text-muted">{{ $staff->date_of_birth?->format('d/m/Y') ?? '---' }}</div>
                        </td>
                        <td>
                            @if($staff->responsibleLocation)
                                <div class="fw-semibold">{{ $staff->responsibleLocation->location_name }}</div>
                                <div class="small text-muted">{{ $staff->responsibleLocation->province }}</div>
                            @else
                                <span class="text-muted small">Chưa phân khu vực</span>
                            @endif
                        </td>
                        <td class="small" style="max-width: 220px;">{{ $staff->certificates ?: '---' }}</td>
                        <td>
                            <span class="badge {{ $staff->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                {{ $staff->status === 'active' ? 'Đang làm việc' : 'Nghỉ việc' }}
                            </span>
                        </td>
                        <td class="small text-muted" style="max-width: 180px;">{{ $staff->note ?: '---' }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm text-warning me-2"
                                onclick='prepareEdit(@json($staff))'
                                data-bs-toggle="modal" data-bs-target="#fieldStaffModal">
                                <i class="fa fa-edit"></i>
                            </button>
                            <a href="javascript:void(0)" class="text-danger" title="Xóa" onclick="handleDelete('{{ $staff->id }}', 'Xóa nhân viên hiện trường {{ $staff->full_name }}?')">
                                <i class="fa fa-trash"></i>
                            </a>
                            <form id="delete-form-{{ $staff->id }}" action="{{ route('field-staff.destroy', $staff->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">Chưa có dữ liệu nhân viên hiện trường.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-top">
        {{ $fieldStaff->links('pagination::bootstrap-5') }}
    </div>
</div>

<div class="modal fade" id="fieldStaffModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="fieldStaffForm" method="POST">
                @csrf
                <div id="methodField"></div>
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title fw-bold" id="modalTitle">Thêm Nhân viên hiện trường</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" id="full_name" class="form-control bg-light border-0" required maxlength="100" placeholder="Nhập họ và tên" oninput="formatName(this)">
                            <div class="invalid-feedback" id="full_name_error" style="display: none;"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="phone" class="form-control bg-light border-0" maxlength="10" inputmode="numeric" required placeholder="VD: 0912345678" oninput="formatPhone(this)">
                            <div class="invalid-feedback" id="phone_error" style="display: none;"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ngày sinh <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_birth" id="date_of_birth" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tài khoản liên kết</label>
                            <select name="user_id" id="user_id" class="form-select bg-light border-0">
                                <option value="">Không liên kết</option>
                                @foreach($fieldUsers as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->name }} - {{ $user->username }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Khu vực phụ trách <span class="text-danger">*</span></label>
                            <select name="responsible_location_id" id="responsible_location_id" class="form-select bg-light border-0" required>
                                <option value="">Chọn kho/bãi</option>
                                @foreach($responsibleLocations as $location)
                                    <option value="{{ $location->id }}">{{ $location->location_name }} - {{ $location->province }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ngày bắt đầu làm việc</label>
                            <input type="text" onfocus="(this.type='date')" onblur="(this.value == '' ? this.type='text' : this.type='date')" placeholder="VD: {{ now()->format('d/m/Y') }}" name="start_date" id="start_date" class="form-control bg-light border-0" min="{{ now()->subYears(10)->toDateString() }}" max="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Trạng thái <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-select bg-light border-0" required>
                                <option value="active">Đang làm việc</option>
                                <option value="inactive">Nghỉ việc</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Chứng chỉ</label>
                            <textarea name="certificates" id="certificates" class="form-control bg-light border-0" rows="3" placeholder="VD: Chứng chỉ an toàn kho bãi; nghiệp vụ hải quan..." maxlength="500"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Ghi chú</label>
                            <textarea name="note" id="note" class="form-control bg-light border-0" rows="3" maxlength="1000" placeholder="Nhập ghi chú (nếu có)"></textarea>
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
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof flatpickr !== 'undefined') {
            flatpickr("#date_of_birth", { dateFormat: "d/m/Y", allowInput: true });
            flatpickr("#start_date", { dateFormat: "d/m/Y", allowInput: true });
        }
    });

    function dateOnly(value) {
        return value ? value.split('T')[0].split(' ')[0] : '';
    }

    function setDateValue(id, date) {
        if (typeof flatpickr !== 'undefined') {
            let instance = flatpickr("#" + id, { dateFormat: "d/m/Y" });
            instance.setDate(date);
        }
    }

    function prepareAdd() {
        document.getElementById('modalTitle').innerText = 'Thêm Nhân viên hiện trường';
        document.getElementById('fieldStaffForm').action = "{{ route('field-staff.store') }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('fieldStaffForm').reset();

        let today = new Date();
        let adultDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
        
        if (window.setDateValue) {
            setDateValue('date_of_birth', adultDate);
        }
    }

    function formatName(input) {
        let val = input.value;
        let errorMsg = '';

        if (/^\s/.test(val)) {
            errorMsg = "Không được nhập khoảng trắng ở đầu.";
        }
        val = val.replace(/^\s+/, '');
        
        if (/\s{2,}/.test(val)) {
            errorMsg = "Chỉ được nhập 1 khoảng trắng giữa các từ.";
        }
        val = val.replace(/\s{2,}/g, ' ');

        if (/[^a-zA-ZÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂưăạảấầẩẫậắằẳẵặẹẻẽềềểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễệỉịọỏốồổỗộớờởỡợụủứừỮỰỲỴÝỶỸửữựỳỵỷỹ\s]/g.test(val)) {
            errorMsg = "Chỉ được nhập chữ cái tiếng Việt, không số hoặc ký tự đặc biệt.";
        }
        val = val.replace(/[^a-zA-ZÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂưăạảấầẩẫậắằẳẵặẹẻẽềềểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễệỉịọỏốồổỗộớờởỡợụủứừỮỰỲỴÝỶỸửữựỳỵỷỹ\s]/g, '');

        val = val.replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
        
        input.value = val;
        
        let errorDiv = document.getElementById('full_name_error');
        if (errorDiv) {
            if (errorMsg) {
                input.classList.add('is-invalid');
                errorDiv.innerText = errorMsg;
                errorDiv.style.display = 'block';
                input.setCustomValidity(errorMsg);
            } else {
                input.classList.remove('is-invalid');
                errorDiv.style.display = 'none';
                input.setCustomValidity('');
            }
        }
    }

    function formatPhone(input) {
        let val = input.value;
        let errorMsg = '';

        val = val.replace(/[^0-9]/g, '');
        
        if (val.length > 0 && val[0] !== '0') {
            errorMsg = "Số điện thoại phải bắt đầu bằng số 0.";
        } else if (val.length > 10) {
            val = val.substring(0, 10);
        } else if (val.length > 0 && val.length < 10) {
            errorMsg = "Số điện thoại phải đủ 10 số.";
        }
        
        input.value = val;
        
        let errorDiv = document.getElementById('phone_error');
        if (errorDiv) {
            if (errorMsg) {
                input.classList.add('is-invalid');
                errorDiv.innerText = errorMsg;
                errorDiv.style.display = 'block';
                input.setCustomValidity(errorMsg);
            } else {
                input.classList.remove('is-invalid');
                errorDiv.style.display = 'none';
                input.setCustomValidity('');
            }
        }
    }

    function prepareEdit(staff) {
        document.getElementById('modalTitle').innerText = 'Chỉnh sửa Nhân viên hiện trường';
        document.getElementById('fieldStaffForm').action = `/field-staff/${staff.id}`;
        document.getElementById('methodField').innerHTML = '@method("PUT")';

        document.getElementById('full_name').value = staff.full_name;
        document.getElementById('phone').value = staff.phone || '';
        document.getElementById('date_of_birth').value = dateOnly(staff.date_of_birth);
        document.getElementById('user_id').value = staff.user_id || '';
        document.getElementById('responsible_location_id').value = staff.responsible_location_id || '';
        document.getElementById('start_date').value = dateOnly(staff.start_date);
        document.getElementById('status').value = staff.status;
        document.getElementById('certificates').value = staff.certificates || '';
        document.getElementById('note').value = staff.note || '';
    }
</script>
@endpush
@endsection
