@extends('layouts.app')

@section('title', 'Quản lý Nhân viên hiện trường')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h4 class="fw-bold mb-0">Quản lý Nhân viên hiện trường</h4>
    <x-export-buttons />
</div>

<div class="card border-0 rounded-4 shadow-sm p-4 mb-4">
    <form action="{{ route('field-staff.index') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Mã nhân viên</label>
            <input type="text" name="staff_code" class="form-control border-light" placeholder="Mã" value="{{ request('staff_code') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Họ tên</label>
            <input type="text" name="full_name" class="form-control border-light" placeholder="Họ tên" value="{{ request('full_name') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Số điện thoại</label>
            <input type="text" name="phone" class="form-control border-light" placeholder="10 số" value="{{ request('phone') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold text-muted">Khu vực phụ trách</label>
            <select name="responsible_location_id" class="form-select border-light">
                <option value="">Tất cả khu vực</option>
                @foreach($responsibleLocations as $location)
                    <option value="{{ $location->id }}" {{ (string) request('responsible_location_id') === (string) $location->id ? 'selected' : '' }}>
                        {{ $location->location_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Ngày sinh</label>
            <input type="text" name="date_of_birth" class="form-control border-light" placeholder="Ngày/Tháng/Năm" value="{{ \App\Support\VietnameseDate::display(request('date_of_birth')) }}" data-date-input data-label="Ngày sinh">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Trạng thái</label>
            <select name="status" class="form-select border-light">
                <option value="">Tất cả trạng thái</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang làm việc</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nghỉ việc</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Chứng chỉ</label>
            <input type="text" name="certificates" class="form-control border-light" placeholder="Chứng chỉ" value="{{ request('certificates') }}">
        </div>
        <div class="col-md-2 ms-md-auto">
            <button type="submit" class="btn btn-navy w-100">Lọc</button>
        </div>
    </form>
</div>

<div class="d-flex justify-content-end mb-3">
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
                                Ngày sinh: {{ $staff->date_of_birth?->format('d/m/Y') ?? '---' }}
                            </div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $staff->phone ?: '---' }}</div>
                            <div class="small text-muted">{{ $staff->date_of_birth?->format('d/m/Y') ?? '---' }}</div>
                        </td>
                        <td>
                            @if($staff->responsibleLocations->isNotEmpty())
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($staff->responsibleLocations as $location)
                                        <span class="badge bg-light text-dark border">{{ $location->location_name }}</span>
                                    @endforeach
                                </div>
                            @elseif($staff->responsibleLocation)
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
        {{ $fieldStaff->appends(request()->query())->links('pagination::bootstrap-5') }}
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
                            <label class="form-label fw-semibold">Họ và tên</label>
                            <input type="text" name="full_name" id="full_name" class="form-control bg-light border-0" data-validate="person-name" data-label="Họ và tên" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Số điện thoại</label>
                            <input type="text" name="phone" id="phone" class="form-control bg-light border-0" maxlength="10" inputmode="numeric" data-validate="phone-vn" data-label="Số điện thoại" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ngày sinh</label>
                            <input type="text" name="date_of_birth" id="date_of_birth" class="form-control bg-light border-0" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Ngày sinh" required>
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
                            <select name="responsible_location_ids[]" id="responsible_location_ids" class="form-select bg-light border-0" multiple data-validate data-label="Khu vực phụ trách" required>
                                @foreach($responsibleLocations as $location)
                                    <option value="{{ $location->id }}">{{ $location->location_name }} - {{ $location->province }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Ngày bắt đầu làm việc</label>
                            <input type="text" name="start_date" id="start_date" class="form-control bg-light border-0" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Ngày bắt đầu làm việc">
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
    function removeTemporaryFieldUserOptions() {
        document.querySelectorAll('#user_id option[data-temporary-linked-user="true"]').forEach((option) => option.remove());
    }

    function ensureFieldUserOption(staff) {
        const select = document.getElementById('user_id');
        removeTemporaryFieldUserOptions();

        if (!staff.user_id || !staff.user) {
            select.value = '';
            return;
        }

        if (!Array.from(select.options).some((option) => option.value === String(staff.user_id))) {
            const option = new Option(`${staff.user.name} - ${staff.user.username || staff.user.email}`, staff.user_id, true, true);
            option.dataset.temporaryLinkedUser = 'true';
            select.add(option);
        }

        select.value = String(staff.user_id);
    }

    function prepareAdd() {
        document.getElementById('modalTitle').innerText = 'Thêm Nhân viên hiện trường';
        document.getElementById('fieldStaffForm').action = "{{ route('field-staff.store') }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('fieldStaffForm').reset();
        removeTemporaryFieldUserOptions();
    }



    function prepareEdit(staff) {
        document.getElementById('modalTitle').innerText = 'Chỉnh sửa Nhân viên hiện trường';
        document.getElementById('fieldStaffForm').action = `/field-staff/${staff.id}`;
        document.getElementById('methodField').innerHTML = '@method("PUT")';

        document.getElementById('full_name').value = staff.full_name;
        document.getElementById('phone').value = staff.phone || '';
        document.getElementById('date_of_birth').value = isoToDate(dateOnly(staff.date_of_birth));
        ensureFieldUserOption(staff);
        const selectedLocations = (staff.responsible_locations || []).map((location) => String(location.id));
        if (selectedLocations.length === 0 && staff.responsible_location_id) {
            selectedLocations.push(String(staff.responsible_location_id));
        }
        Array.from(document.getElementById('responsible_location_ids').options).forEach((option) => {
            option.selected = selectedLocations.includes(option.value);
        });
        document.getElementById('start_date').value = isoToDate(dateOnly(staff.start_date));
        document.getElementById('status').value = staff.status;
        document.getElementById('certificates').value = staff.certificates || '';
        document.getElementById('note').value = staff.note || '';
    }
</script>
@endpush
@endsection
