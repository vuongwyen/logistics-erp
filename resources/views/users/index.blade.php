@extends('layouts.app')

@section('title', 'Quản lý Nhân sự - NT Logistics')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-navy">Danh sách Nhân viên</h4>
                <p class="text-muted small">Quản lý tài khoản và phân quyền truy cập hệ thống.</p>
            </div>
            <x-export-buttons />
        </div>
    </div>

    <div class="card border-0 rounded-4 shadow-sm p-4 mb-4">
        <form action="{{ route('users.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Mã nhân sự</label>
                <input type="text" name="employee_code" class="form-control border-light" placeholder="Mã" value="{{ request('employee_code') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Họ tên</label>
                <input type="text" name="name" class="form-control border-light" placeholder="Họ tên" value="{{ request('name') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Email</label>
                <input type="text" name="email" class="form-control border-light" placeholder="Email" value="{{ request('email') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Chức vụ</label>
                <input type="text" name="position" class="form-control border-light" placeholder="Chức vụ" value="{{ request('position') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Bộ phận</label>
                <input type="text" name="department" class="form-control border-light" placeholder="Bộ phận" value="{{ request('department') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Vai trò</label>
                <select name="role_id" class="form-select border-light">
                    <option value="">Vai trò</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ (string) request('role_id') === (string) $role->id ? 'selected' : '' }}>{{ $role->role_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Ngày sinh</label>
                <input type="text" name="date_of_birth" class="form-control border-light" placeholder="Ngày/Tháng/Năm" value="{{ \App\Support\VietnameseDate::display(request('date_of_birth')) }}" data-date-input data-label="Ngày sinh">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Ngày tham gia</label>
                <input type="text" name="joined_at" class="form-control border-light" placeholder="Ngày/Tháng/Năm" value="{{ \App\Support\VietnameseDate::display(request('joined_at')) }}" data-date-input data-label="Ngày tham gia">
            </div>
            <div class="col-md-2 ms-md-auto">
                <button type="submit" class="btn btn-navy w-100">Lọc</button>
            </div>
        </form>
    </div>
    
    <div class="d-flex justify-content-end mb-4">
        <a href="{{ route('users.create') }}" class="btn btn-navy fw-bold px-4">
            <i class="fa fa-user-plus me-2"></i> THÊM NHÂN VIÊN
        </a>
    </div>

    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('users.create') }}" class="btn btn-navy fw-bold px-4">
            <i class="fa fa-user-plus me-2"></i> THÊM NHÂN VIÊN
        </a>
    </div>

    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã / Họ tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Chức vụ</th>
                            <th>Bộ phận</th>
                            <th>Ngày sinh</th>
                            <th>Ngày tham gia</th>
                            <th class="text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-navy text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $user->employee_code ?? '---' }}</div>
                                            <div class="small text-muted">{{ $user->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3">
                                        {{ $user->role->role_name }}
                                    </span>
                                </td>
                                <td>{{ $user->position ?? '---' }}</td>
                                <td>{{ $user->department ?? '---' }}</td>
                                <td>{{ $user->date_of_birth?->format('d/m/Y') ?? '---' }}</td>
                                <td>{{ $user->joined_at?->format('d/m/Y') ?? $user->created_at->format('d/m/Y') }}</td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-navy">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Xác nhận xóa tài khoản này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchEmail = document.getElementById('search_email');
        const emailError = document.getElementById('search_email_error');
        const searchForm = document.getElementById('search_form');

        function doValidateEmail() {
            if (!searchEmail) return true;
            let originalVal = searchEmail.value;
            let val = originalVal;
            let hasError = false;
            let errorMsg = '';

            // Xóa tất cả khoảng trắng trong email
            if (/\s/.test(val)) {
                val = val.replace(/\s/g, '');
                hasError = true;
                errorMsg = 'Email không được chứa khoảng trắng.';
            }

            // Chỉ cho phép chữ, số, @ và .
            const invalidCharsRegex = /[^a-zA-Z0-9@.]/g;
            if (invalidCharsRegex.test(val)) {
                val = val.replace(invalidCharsRegex, '');
                hasError = true;
                errorMsg = 'Email chỉ được chứa chữ, số, @ và dấu chấm.';
            }

            // Chỉ cho phép 1 ký tự @
            if ((val.match(/@/g) || []).length > 1) {
                const firstAt = val.indexOf('@');
                val = val.substring(0, firstAt + 1) + val.substring(firstAt + 1).replace(/@/g, '');
                hasError = true;
                errorMsg = 'Email chỉ được chứa tối đa 1 ký tự @.';
            }

            // Chỉ cho phép 1 ký tự .
            if ((val.match(/\./g) || []).length > 1) {
                const firstDot = val.indexOf('.');
                val = val.substring(0, firstDot + 1) + val.substring(firstDot + 1).replace(/\./g, '');
                hasError = true;
                errorMsg = 'Email chỉ được chứa tối đa 1 dấu chấm.';
            }

            if (originalVal !== val) {
                let start = searchEmail.selectionStart;
                let end = searchEmail.selectionEnd;
                let diff = originalVal.length - val.length;
                searchEmail.value = val;
                if (start !== null && end !== null) {
                    searchEmail.setSelectionRange(Math.max(0, start - diff), Math.max(0, end - diff));
                }
            }

            // Phân tích lỗi định dạng cụ thể (optional cho ô search, nhưng để đầy đủ theo yêu cầu)
            let isValidFormat = true;
            let formatErrorMsg = '';
            if (val.length > 0) {
                if (!val.includes('@')) {
                    isValidFormat = false;
                    formatErrorMsg = 'Email đang thiếu ký tự @.';
                } else {
                    let parts = val.split('@');
                    if (parts[0].length === 0) {
                        isValidFormat = false;
                        formatErrorMsg = 'Email đang thiếu tên trước @.';
                    } else if (parts.length > 2) {
                        isValidFormat = false;
                        formatErrorMsg = 'Email không được chứa nhiều hơn 1 ký tự @.';
                    } else if (parts[1].length === 0) {
                        isValidFormat = false;
                        formatErrorMsg = 'Email đang thiếu tên miền (sau @).';
                    } else if (!parts[1].includes('.')) {
                        isValidFormat = false;
                        formatErrorMsg = 'Tên miền email đang thiếu dấu chấm (.).';
                    } else {
                        let domainParts = parts[1].split('.');
                        if (domainParts[domainParts.length - 1].length < 2) {
                            isValidFormat = false;
                            formatErrorMsg = 'Tên miền email chưa đầy đủ hoặc không hợp lệ.';
                        }
                    }
                }
            }

            if (hasError || (val.length > 0 && !isValidFormat)) {
                searchEmail.classList.add('is-invalid');
                if (!hasError) errorMsg = formatErrorMsg;
                emailError.textContent = errorMsg;
                return false;
            } else {
                searchEmail.classList.remove('is-invalid');
                return true;
            }
        }

        let emailValidationTimeout;
        if (searchEmail) {
            searchEmail.addEventListener('input', function() {
                clearTimeout(emailValidationTimeout);
                emailValidationTimeout = setTimeout(doValidateEmail, 50);
            });
            // Trigger on load for pre-filled data
            if (searchEmail.value) {
                searchEmail.dispatchEvent(new Event('input'));
            }
        }

        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                if (searchEmail.value.length > 0) {
                    const isEmailValid = doValidateEmail();
                    if (!isEmailValid) {
                        e.preventDefault();
                    }
                }
            });
        }
    });
</script>
@endpush
