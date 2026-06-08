@extends('layouts.app')

@section('title', 'Chỉnh sửa Nhân viên - NT Logistics')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('users.index') }}" class="text-navy text-decoration-none small fw-bold">
                <i class="fa fa-arrow-left me-1"></i> Quay lại danh sách
            </a>
            <h4 class="fw-bold mt-2 text-navy">Chỉnh sửa Tài khoản: {{ $user->name }}</h4>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-0 rounded-4 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('users.update', $user) }}" method="POST" id="user_form">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Họ tên nhân viên</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" data-validate="person-name" data-label="Họ tên nhân viên" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Email đăng nhập</label>
                            <input type="email" id="profile_email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            <div id="profile_email_error" class="invalid-feedback"></div>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Mã nhân sự</label>
                                <input type="text" class="form-control bg-light fw-bold text-navy" value="{{ $user->employee_code ?? '---' }}" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Bộ phận / Phòng ban</label>
                                <select id="department" name="department" class="form-select @error('department') is-invalid @enderror" required>
                                    <option value="">-- Chọn bộ phận --</option>
                                    @foreach($departments as $value => $label)
                                        <option value="{{ $value }}" {{ old('department', $user->department) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('department') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Chức vụ</label>
                                <select id="position" name="position" class="form-select @error('position') is-invalid @enderror" required>
                                    <option value="">-- Chọn bộ phận trước --</option>
                                </select>
                                @error('position') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ngày sinh</label>
                                <input type="text" name="date_of_birth" class="form-control" value="{{ \App\Support\VietnameseDate::display(old('date_of_birth', $user->date_of_birth)) }}" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Ngày sinh" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Ngày tham gia</label>
                                <input type="text" name="joined_at" class="form-control" value="{{ \App\Support\VietnameseDate::display(old('joined_at', $user->joined_at ?? now()->toDateString())) }}" placeholder="Ngày/Tháng/Năm" data-date-input data-label="Ngày tham gia" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Vai trò hệ thống</label>
                            <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->role_name }} ({{ $role->role_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="p-3 bg-light rounded-3 mb-4 border-start border-4 border-warning">
                            <h6 class="fw-bold text-navy mb-1 small">Đổi mật khẩu (Tùy chọn)</h6>
                            <p class="small text-muted mb-3">Chỉ nhập nếu bạn muốn thay đổi mật khẩu cho nhân viên này.</p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Mật khẩu mới</label>
                                    <div class="input-group input-group-sm">
                                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="password-requirements" class="mt-2 small d-none">
                                        <div class="text-muted mb-1 fw-semibold">Yêu cầu mật khẩu:</div>
                                        <ul class="list-unstyled mb-0" style="font-size: 0.8rem;">
                                            <li id="req_length" class="text-danger"><i class="fa fa-times-circle me-1"></i> Tối thiểu 8 ký tự</li>
                                            <li id="req_lower" class="text-danger"><i class="fa fa-times-circle me-1"></i> Có chữ thường (a-z)</li>
                                            <li id="req_upper" class="text-danger"><i class="fa fa-times-circle me-1"></i> Có chữ hoa (A-Z)</li>
                                            <li id="req_number" class="text-danger"><i class="fa fa-times-circle me-1"></i> Có số (0-9)</li>
                                            <li id="req_special" class="text-danger"><i class="fa fa-times-circle me-1"></i> Có ký tự đặc biệt</li>
                                        </ul>
                                    </div>
                                    @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Xác nhận mật khẩu</label>
                                    <div class="input-group input-group-sm">
                                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                                        <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </div>
                                    <div id="password-match-msg" class="form-text mt-2" style="display: none;"></div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-navy w-100 fw-bold py-2">
                            <i class="fa fa-save me-2"></i> CẬP NHẬT THÔNG TIN
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const DEPT_POSITION_MAP = @json(\App\Support\LogisticsOptions::departmentPositionMap());
    const OLD_POSITION = '{{ old('position', $user->position) }}';
    const OLD_DEPARTMENT = '{{ old('department', $user->department) }}';

    document.addEventListener('DOMContentLoaded', function () {
        const deptSelect = document.getElementById('department');
        const posSelect = document.getElementById('position');
        const emailInput = document.querySelector('input[name="email"]');
        const submitBtn = document.querySelector('button[type="submit"]');

        /** --- Cascading dropdown --- */
        function populatePositions(department, selectedPosition) {
            posSelect.innerHTML = '';
            const positions = DEPT_POSITION_MAP[department] || [];
            if (positions.length === 0) {
                posSelect.innerHTML = '<option value="">-- Chọn bộ phận trước --</option>';
                posSelect.disabled = true;
                return;
            }
            posSelect.disabled = false;
            posSelect.innerHTML = '<option value="">-- Chọn chức vụ --</option>';
            positions.forEach(function (pos) {
                const opt = document.createElement('option');
                opt.value = pos;
                opt.textContent = pos;
                if (pos === selectedPosition) { opt.selected = true; }
                posSelect.appendChild(opt);
            });
        }

        // Init on page load: restore existing user values (or old() after validation failure)
        if (OLD_DEPARTMENT) {
            populatePositions(OLD_DEPARTMENT, OLD_POSITION);
        } else {
            posSelect.disabled = true;
        }

        deptSelect.addEventListener('change', function () {
            populatePositions(this.value, '');
        });

        /** --- Name and Email Validation --- */
        const userForm = document.getElementById('user_form');
        const profileName = document.getElementById('profile_name');
        const profileEmail = document.getElementById('profile_email');
        const nameError = document.getElementById('profile_name_error');
        const emailError = document.getElementById('profile_email_error');

        function doValidateName() {
            if (!profileName) return true;
            let originalVal = profileName.value;
            let val = originalVal;
            let hasError = false;
            let errorMsg = '';

            // Xóa khoảng trắng ở đầu
            if (val.startsWith(' ')) {
                val = val.replace(/^\s+/, '');
                hasError = true;
                errorMsg = 'Không được nhập khoảng trắng ở vị trí đầu tiên.';
            }

            // Xóa khoảng trắng thừa (nhiều hơn 1 dấu cách)
            if (/\s{2,}/.test(val)) {
                val = val.replace(/\s{2,}/g, ' ');
                hasError = true;
                errorMsg = 'Không được nhập quá 1 khoảng trắng giữa các từ.';
            }

            // Xóa số và ký tự đặc biệt, hỗ trợ các ký tự Unicode bằng \p{L} (chữ) và \p{M} (dấu)
            const invalidCharsRegex = /[^\p{L}\p{M}\s]/gu;
            if (invalidCharsRegex.test(val)) {
                val = val.replace(invalidCharsRegex, '');
                hasError = true;
                errorMsg = 'Họ và tên không được chứa số hoặc ký tự đặc biệt.';
            }

            // Chuẩn hóa viết hoa chữ cái đầu của mỗi từ
            let capitalizedVal = val.split(' ').map(word => {
                if (word.length === 0) return '';
                return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
            }).join(' ');

            if (val !== capitalizedVal) {
                val = capitalizedVal;
            }

            if (originalVal !== val) {
                let start = profileName.selectionStart;
                let end = profileName.selectionEnd;
                let diff = originalVal.length - val.length;
                profileName.value = val;
                if (start !== null && end !== null) {
                    profileName.setSelectionRange(Math.max(0, start - diff), Math.max(0, end - diff));
                }
            }

            if (hasError) {
                profileName.classList.add('is-invalid');
                nameError.textContent = errorMsg;
                return false;
            } else {
                profileName.classList.remove('is-invalid');
                return true;
            }
        }

        function doValidateEmail() {
            if (!profileEmail) return true;
            let originalVal = profileEmail.value;
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
                let start = profileEmail.selectionStart;
                let end = profileEmail.selectionEnd;
                let diff = originalVal.length - val.length;
                profileEmail.value = val;
                if (start !== null && end !== null) {
                    profileEmail.setSelectionRange(Math.max(0, start - diff), Math.max(0, end - diff));
                }
            }

            // Phân tích lỗi định dạng cụ thể
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
                profileEmail.classList.add('is-invalid');
                if (!hasError) errorMsg = formatErrorMsg;
                emailError.textContent = errorMsg;
                return false;
            } else {
                profileEmail.classList.remove('is-invalid');
                return true;
            }
        }

        let nameValidationTimeout;
        if (profileName) {
            profileName.addEventListener('input', function() {
                clearTimeout(nameValidationTimeout);
                nameValidationTimeout = setTimeout(doValidateName, 50);
            });
            // Trigger on load for pre-filled data
            profileName.dispatchEvent(new Event('input'));
        }
        
        let emailValidationTimeout;
        if (profileEmail) {
            profileEmail.addEventListener('input', function() {
                clearTimeout(emailValidationTimeout);
                emailValidationTimeout = setTimeout(doValidateEmail, 50);
            });
            // Trigger on load for pre-filled data
            profileEmail.dispatchEvent(new Event('input'));
        }

        if (userForm) {
            userForm.addEventListener('submit', function(e) {
                const isNameValid = doValidateName();
                const isEmailValid = doValidateEmail();
                
                if (typeof isMatchValid !== 'undefined' && !isMatchValid && document.getElementById('password') && document.getElementById('password').value.length > 0) {
                    e.preventDefault();
                    return;
                }

                if (!isNameValid || !isEmailValid) {
                    e.preventDefault();
                }
            });
        }

        /** --- Password Real-time Validation & Toggle --- */
        const pwdInput = document.getElementById('password');
        const pwdConfInput = document.getElementById('password_confirmation');
        const reqsBox = document.getElementById('password-requirements');
        const pwdMatchMsg = document.getElementById('password-match-msg');
        let isMatchValid = false;
        
        const reqs = {
            length: { el: document.getElementById('req_length'), regex: /.{8,}/ },
            lower: { el: document.getElementById('req_lower'), regex: /[a-z]/ },
            upper: { el: document.getElementById('req_upper'), regex: /[A-Z]/ },
            number: { el: document.getElementById('req_number'), regex: /[0-9]/ },
            special: { el: document.getElementById('req_special'), regex: /[^A-Za-z0-9]/ }
        };

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });

        function updateRequirement(reqKey, val) {
            const item = reqs[reqKey];
            if (item.regex.test(val)) {
                item.el.classList.add('d-none');
            } else {
                item.el.classList.remove('d-none');
            }
        }

        if (pwdInput) {
            pwdInput.addEventListener('input', function() {
                const val = this.value;
                if (val.length === 0) {
                    reqsBox.classList.add('d-none');
                    // clear confirmation
                    if (pwdConfInput) {
                        pwdConfInput.value = '';
                        pwdMatchMsg.style.display = 'none';
                        pwdConfInput.classList.remove('is-invalid', 'is-valid');
                    }
                    return;
                }
                
                reqsBox.classList.remove('d-none');
                
                let allMet = true;
                Object.keys(reqs).forEach(key => {
                    updateRequirement(key, val);
                    if (!reqs[key].regex.test(val)) allMet = false;
                });

                if (allMet) {
                    reqsBox.classList.add('d-none');
                }
                
                // Re-trigger confirmation check if there's already some value
                if (pwdConfInput && pwdConfInput.value.length > 0) {
                    pwdConfInput.dispatchEvent(new Event('input'));
                }
            });
        }

        if (pwdConfInput) {
            pwdConfInput.addEventListener('input', function() {
                const val = this.value;
                const pwdVal = pwdInput ? pwdInput.value : '';
                
                if (val.length === 0) {
                    pwdMatchMsg.style.display = 'none';
                    pwdConfInput.classList.remove('is-invalid', 'is-valid');
                    isMatchValid = false;
                    return;
                }

                pwdMatchMsg.style.display = 'block';
                if (val === pwdVal) {
                    pwdMatchMsg.className = 'form-text text-success fw-bold mt-2';
                    pwdMatchMsg.innerHTML = '<i class="fa fa-check-circle me-1"></i> Mật khẩu khớp';
                    pwdConfInput.classList.remove('is-invalid');
                    pwdConfInput.classList.add('is-valid');
                    isMatchValid = true;
                } else {
                    pwdMatchMsg.className = 'form-text text-danger mt-2';
                    pwdMatchMsg.innerHTML = '<i class="fa fa-times-circle me-1"></i> Mật khẩu không khớp';
                    pwdConfInput.classList.remove('is-valid');
                    pwdConfInput.classList.add('is-invalid');
                    isMatchValid = false;
                }
            });
        }

    });
</script>
@endpush
