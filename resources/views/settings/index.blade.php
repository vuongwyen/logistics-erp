@extends('layouts.app')

@section('title', 'Cài đặt Hệ thống - NT Logistics')

@section('content')
@php
    $user = auth()->user();
    $isAdmin = $user->hasRole('ADMIN');
    $companySettings = $settings->get('company', collect())->keyBy('key');

    $usdRate      = $systemParams->get('system.usd_rate')?->value ?? '25450';
    $vatPercent   = $systemParams->get('system.vat_percent')?->value ?? '10';
    $fuelLimit    = $systemParams->get('system.fuel_limit')?->value ?? '5000000';
    $tollLimit    = $systemParams->get('system.toll_limit')?->value ?? '2000000';
    $overageAlert = $systemParams->get('system.overage_alert')?->value ?? '0';

    $roleBadgeColor = match($user->role?->role_code) {
        'ADMIN'      => 'danger',
        'ACCOUNTANT' => 'success',
        'DISPATCH'   => 'warning',
        'SALES'      => 'info',
        'DRIVER'     => 'secondary',
        'FIELD'      => 'primary',
        default      => 'dark',
    };

    // Xác định tab nào cần mở sau khi redirect (flash từ form submit)
    $activeTab = session('active_tab', 'tab-hosoca');
@endphp

<style>
    .settings-nav .nav-link {
        display: flex;
        align-items: center;
        gap: .6rem;
        padding: .75rem 1.25rem;
        border-radius: .75rem !important;
        color: #495057;
        font-weight: 600;
        font-size: .875rem;
        transition: background .15s, color .15s;
        border: none !important;
        margin-bottom: .25rem;
    }
    .settings-nav .nav-link:hover {
        background: rgba(26,35,126,.07);
        color: #1a237e;
    }
    .settings-nav .nav-link.active {
        background: #1a237e;
        color: #fff !important;
    }
    .settings-nav .nav-link.active .nav-icon { color: #fff !important; }
    .settings-nav .nav-link .nav-icon { font-size: 1rem; width: 1.25rem; text-align:center; }
    .settings-nav .nav-link .badge-coming { font-size: .6rem; }
    .tab-pane { animation: fadeIn .2s ease; }
    @keyframes fadeIn { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:none; } }
</style>

<div class="container-fluid py-4">

    {{-- Tiêu đề trang --}}
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold text-navy"><i class="fa fa-sliders me-2"></i>Cài đặt Hệ thống</h4>
            <p class="text-muted small mb-0">Quản lý hồ sơ cá nhân, tham số vận hành, phân quyền và dữ liệu hệ thống.</p>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success border-0 rounded-3 d-flex align-items-center gap-2 mb-3">
            <i class="fa fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 rounded-3 d-flex align-items-center gap-2 mb-3">
            <i class="fa fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="row g-4">

        {{-- ============================================================ --}}
        {{-- SIDEBAR: Tab navigation dọc --}}
        {{-- ============================================================ --}}
        <div class="col-lg-3">
            <div class="card border-0 rounded-4 shadow-sm sticky-top" style="top:1rem;">
                {{-- Avatar + tên --}}
                <div class="card-body p-4 text-center border-bottom">
                    <div class="bg-navy text-white rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-3"
                         style="width:64px;height:64px;font-size:1.6rem;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="fw-bold text-navy">{{ $user->name }}</div>
                    <div class="text-muted small">{{ $user->email }}</div>
                    <span class="badge bg-{{ $roleBadgeColor }} rounded-pill px-3 mt-2">
                        {{ $user->role?->role_name ?? $user->role?->role_code }}
                    </span>
                </div>

                {{-- Menu tabs --}}
                <div class="card-body p-3">
                    <div class="nav flex-column settings-nav" id="settingsNav" role="tablist">

                        <a class="nav-link {{ $activeTab === 'tab-hosoca' ? 'active' : '' }}"
                           id="nav-hosoca" data-bs-toggle="tab" href="#tab-hosoca" role="tab">
                            <i class="fa fa-user nav-icon"></i>
                            <span>Hồ sơ cá nhân</span>
                        </a>

                        <a class="nav-link {{ $activeTab === 'tab-baomat' ? 'active' : '' }}"
                           id="nav-baomat" data-bs-toggle="tab" href="#tab-baomat" role="tab">
                            <i class="fa fa-lock nav-icon"></i>
                            <span>Bảo mật & hệ thống</span>
                        </a>

                        @if($isAdmin)
                        <a class="nav-link {{ $activeTab === 'tab-cauhinh' ? 'active' : '' }}"
                           id="nav-cauhinh" data-bs-toggle="tab" href="#tab-cauhinh" role="tab">
                            <i class="fa fa-cogs nav-icon"></i>
                            <span>Tham số hệ thống</span>
                        </a>

                        <a class="nav-link {{ $activeTab === 'tab-phanquyen' ? 'active' : '' }}"
                           id="nav-phanquyen" data-bs-toggle="tab" href="#tab-phanquyen" role="tab">
                            <i class="fa fa-shield nav-icon"></i>
                            <span>Phân quyền & biểu mẫu</span>
                        </a>

                        <a class="nav-link {{ $activeTab === 'tab-luutru' ? 'active' : '' }}"
                           id="nav-luutru" data-bs-toggle="tab" href="#tab-luutru" role="tab">
                            <i class="fa fa-database nav-icon"></i>
                            <span>Lưu trữ dữ liệu</span>
                        </a>

                        <a class="nav-link {{ $activeTab === 'tab-trienkhai' ? 'active' : '' }}"
                           id="nav-trienkhai" data-bs-toggle="tab" href="#tab-trienkhai" role="tab">
                            <i class="fa fa-box nav-icon"></i>
                            <span>Gói triển khai (All-in-One)</span>
                        </a>
                        @endif

                    </div>
                </div>

                {{-- Lưu ý bảo mật --}}
                @if($isAdmin)
                <div class="card-body pt-0 px-3 pb-3">
                    <div class="p-3 bg-warning bg-opacity-10 rounded-3 border border-warning border-opacity-25">
                        <div class="small fw-bold text-warning mb-1">
                            <i class="fa fa-exclamation-triangle me-1"></i>Lưu ý bảo mật
                        </div>
                        <div class="small text-muted">Mọi thao tác quản trị được ghi vết trong nhật ký hệ thống.</div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- NỘI DUNG: Tab panels --}}
        {{-- ============================================================ --}}
        <div class="col-lg-9">
            <div class="tab-content" id="settingsTabContent">

                {{-- ---------------------------------------------------- --}}
                {{-- TAB 1: HỒ SƠ CÁ NHÂN --}}
                {{-- ---------------------------------------------------- --}}
                <div class="tab-pane fade {{ $activeTab === 'tab-hosoca' ? 'show active' : '' }}"
                     id="tab-hosoca" role="tabpanel">
                    <div class="card border-0 rounded-4 shadow-sm">
                        <div class="card-header bg-white border-0 p-4 pb-0">
                            <h6 class="fw-bold text-navy text-uppercase mb-1" style="letter-spacing:.05em;">
                                <i class="fa fa-user me-2"></i>Hồ Sơ Cá Nhân
                            </h6>
                            <p class="text-muted small mb-0">Cập nhật thông tin cơ bản của tài khoản</p>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('profile.update') }}" method="POST" id="profile_form">
                                @csrf
                                <input type="hidden" name="_active_tab" value="tab-hosoca">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-navy small">Họ và tên</label>
                                        <input type="text" name="name" id="profile_name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                        <div id="profile_name_error" class="invalid-feedback"></div>
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-navy small">Email</label>
                                        <input type="text" name="email" id="profile_email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                        <div id="profile_email_error" class="invalid-feedback"></div>
                                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Bộ phận</label>
                                        <input type="text" class="form-control bg-light" value="{{ $user->department ?? '---' }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small">Chức vụ</label>
                                        <input type="text" class="form-control bg-light" value="{{ $user->position ?? '---' }}" readonly>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-navy px-4 fw-bold">
                                        <i class="fa fa-save me-2"></i>LƯU THAY ĐỔI
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ---------------------------------------------------- --}}
                {{-- TAB: BẢO MẬT & HỆ THỐNG --}}
                {{-- ---------------------------------------------------- --}}
                <div class="tab-pane fade {{ $activeTab === 'tab-baomat' ? 'show active' : '' }}"
                     id="tab-baomat" role="tabpanel">
                    <div class="card border-0 rounded-4 shadow-sm">
                        <div class="card-header bg-white border-0 p-4 pb-0">
                            <h6 class="fw-bold text-navy text-uppercase mb-1" style="letter-spacing:.05em;">
                                <i class="fa fa-lock me-2"></i>Bảo Mật & Hệ Thống
                            </h6>
                            <p class="text-muted small mb-0">Đổi mật khẩu và cài đặt giao diện</p>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('profile.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="_active_tab" value="tab-baomat">
                                <div class="row g-4">
                                    {{-- Đổi mật khẩu --}}
                                    <div class="col-md-12">
                                        <div class="p-3 bg-light rounded-3 border">
                                            <h6 class="fw-bold mb-3 small text-uppercase">Đổi Mật Khẩu</h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted">Mật khẩu mới</label>
                                                    <div class="input-group">
                                                        <input type="password" name="password" id="new_password" class="form-control @error('password') is-invalid @enderror" placeholder="Nhập mật khẩu mới">
                                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password" tabindex="-1">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                                    
                                                    {{-- Password Checklist --}}
                                                    <div id="password_requirements" class="mt-2 small d-none">
                                                        <div class="text-muted mb-1 fw-semibold">Yêu cầu mật khẩu:</div>
                                                        <ul class="list-unstyled mb-0" style="font-size: 0.8rem;">
                                                            <li id="req_length" class="text-danger"><i class="fa fa-times-circle me-1"></i> Tối thiểu 8 ký tự</li>
                                                            <li id="req_lower" class="text-danger"><i class="fa fa-times-circle me-1"></i> Có chữ thường (a-z)</li>
                                                            <li id="req_upper" class="text-danger"><i class="fa fa-times-circle me-1"></i> Có chữ hoa (A-Z)</li>
                                                            <li id="req_number" class="text-danger"><i class="fa fa-times-circle me-1"></i> Có số (0-9)</li>
                                                            <li id="req_special" class="text-danger"><i class="fa fa-times-circle me-1"></i> Có ký tự đặc biệt</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small text-muted">Xác nhận mật khẩu mới</label>
                                                    <div class="input-group">
                                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Nhập lại mật khẩu mới">
                                                        <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation" tabindex="-1">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </div>
                                                    <div id="req_match" class="small mt-1 text-danger d-none"><i class="fa fa-times-circle me-1"></i> Mật khẩu xác nhận chưa khớp</div>
                                                </div>
                                            </div>
                                            <div class="form-text mt-2">Bỏ trống nếu không muốn đổi mật khẩu.</div>
                                        </div>
                                    </div>

                                    {{-- Cài đặt hiển thị --}}
                                    <div class="col-md-12">
                                        <h6 class="fw-bold mb-3 small text-uppercase">Tùy Chỉnh Hiển Thị</h6>
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label small text-muted">Màu chủ đạo</label>
                                                <input type="color" name="theme_color" class="form-control form-control-color w-100" value="{{ old('theme_color', $user->theme_color ?? '#1a237e') }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small text-muted">Múi giờ</label>
                                                <select name="timezone" class="form-select">
                                                    @foreach(['Asia/Ho_Chi_Minh' => 'Việt Nam', 'Asia/Bangkok' => 'Bangkok', 'UTC' => 'UTC'] as $value => $label)
                                                        <option value="{{ $value }}" {{ old('timezone', $user->timezone ?? 'Asia/Ho_Chi_Minh') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small text-muted">Định dạng ngày</label>
                                                <select name="date_format" class="form-select">
                                                    @foreach(['d/m/Y' => '21/05/2026', 'Y-m-d' => '2026-05-21', 'd-m-Y' => '21-05-2026'] as $value => $label)
                                                        <option value="{{ $value }}" {{ old('date_format', $user->date_format ?? 'd/m/Y') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 pt-2">
                                                <input type="hidden" name="is_dark_mode" value="0">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="is_dark_mode" id="darkModeSwitch" value="1" {{ old('is_dark_mode', $user->is_dark_mode) ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold text-navy" for="darkModeSwitch">Chế độ tối</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 pt-2">
                                                <input type="hidden" name="two_factor_enabled" value="0">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" name="two_factor_enabled" id="twoFactorSwitch" value="1" {{ old('two_factor_enabled', $user->two_factor_enabled) ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bold text-navy" for="twoFactorSwitch">Xác thực 2 yếu tố</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-navy px-4 fw-bold">
                                        <i class="fa fa-save me-2"></i>LƯU THAY ĐỔI
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ---------------------------------------------------- --}}
                {{-- TAB 2: THAM SỐ HỆ THỐNG (ADMIN only) --}}
                {{-- ---------------------------------------------------- --}}
                @if($isAdmin)
                <div class="tab-pane fade {{ $activeTab === 'tab-cauhinh' ? 'show active' : '' }}"
                     id="tab-cauhinh" role="tabpanel">
                    <div class="card border-0 rounded-4 shadow-sm">
                        <div class="card-header bg-white border-0 p-4 pb-0">
                            <h6 class="fw-bold text-navy text-uppercase mb-1" style="letter-spacing:.05em;">
                                <i class="fa fa-cogs me-2"></i>Cấu Hình Tham Số Hệ Thống
                            </h6>
                            <p class="text-muted small mb-0">Tỷ giá, thuế, hạn mức chi phí vận hành — áp dụng toàn hệ thống</p>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('settings.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="_active_tab" value="tab-cauhinh">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Tỷ giá quy đổi (USD/VND)</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">$</span>
                                            <input type="number" name="system_params[system.usd_rate]"
                                                   class="form-control border-start-0" value="{{ $usdRate }}"
                                                   min="1" step="1" required>
                                            <span class="input-group-text bg-light">VND</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Thuế GTGT mặc định (%)</label>
                                        <div class="input-group">
                                            <input type="number" name="system_params[system.vat_percent]"
                                                   class="form-control" value="{{ $vatPercent }}"
                                                   min="0" max="100" step="0.5" required>
                                            <span class="input-group-text bg-light">%</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Hạn mức tiền dầu — Trần (VND)</label>
                                        <div class="input-group">
                                            <input type="number" name="system_params[system.fuel_limit]"
                                                   class="form-control" value="{{ $fuelLimit }}"
                                                   min="0" step="100000" required>
                                            <span class="input-group-text bg-light">₫</span>
                                        </div>
                                        <div class="form-text">Mỗi lệnh điều vận không được vượt mức này</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Hạn mức phí cầu đường — Trần (VND)</label>
                                        <div class="input-group">
                                            <input type="number" name="system_params[system.toll_limit]"
                                                   class="form-control" value="{{ $tollLimit }}"
                                                   min="0" step="100000" required>
                                            <span class="input-group-text bg-light">₫</span>
                                        </div>
                                        <div class="form-text">Mỗi lệnh điều vận không được vượt mức này</div>
                                    </div>
                                    <div class="col-12 pt-1">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                   name="system_params[system.overage_alert]"
                                                   id="overageAlert" value="1"
                                                   {{ $overageAlert === '1' ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="overageAlert">
                                                Bật cảnh báo vượt định mức
                                                <span class="text-muted small fw-normal ms-1">— Hiện thông báo khi chi phí vượt hạn mức</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning fw-bold px-5">
                                        <i class="fa fa-save me-2"></i>Lưu cấu hình
                                    </button>
                                    <a href="{{ route('settings.index') }}" class="btn btn-outline-secondary px-4">Hủy</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ---------------------------------------------------- --}}
                {{-- TAB 3: PHÂN QUYỀN & BIỂU MẪU (ADMIN only) --}}
                {{-- ---------------------------------------------------- --}}
                <div class="tab-pane fade {{ $activeTab === 'tab-phanquyen' ? 'show active' : '' }}"
                     id="tab-phanquyen" role="tabpanel">
                    <div class="card border-0 rounded-4 shadow-sm">
                        <div class="card-header bg-white border-0 p-4 pb-0">
                            <h6 class="fw-bold text-navy text-uppercase mb-1" style="letter-spacing:.05em;">
                                <i class="fa fa-shield me-2"></i>Quản Lý Phân Quyền & Biểu Mẫu
                            </h6>
                            <p class="text-muted small mb-0">Cấu hình vai trò, mẫu in ấn và kiểm soát truy cập hệ thống</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">

                                {{-- Điều hành --}}
                                <div class="col-md-5">
                                    <h6 class="fw-bold text-muted text-uppercase small mb-3" style="letter-spacing:.05em;">Điều hành hệ thống</h6>
                                    <div class="d-grid gap-3">
                                        <a href="{{ route('users.index') }}" class="btn btn-outline-navy fw-bold py-3 text-start d-flex align-items-center gap-3">
                                            <span class="p-2 bg-navy bg-opacity-10 rounded-3">
                                                <i class="fa fa-users-cog text-navy fs-5"></i>
                                            </span>
                                            <span>
                                                <div class="fw-bold">Chỉnh sửa Ma trận Quyền</div>
                                                <div class="small text-muted fw-normal">Quản lý vai trò và phân quyền truy cập</div>
                                            </span>
                                        </a>
                                        <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary fw-bold py-3 text-start d-flex align-items-center gap-3">
                                            <span class="p-2 bg-secondary bg-opacity-10 rounded-3">
                                                <i class="fa fa-history text-secondary fs-5"></i>
                                            </span>
                                            <span>
                                                <div class="fw-bold d-flex align-items-center gap-2">
                                                    Nhật Ký Hoạt Động
                                                </div>
                                                <div class="small text-muted fw-normal">Lịch sử thao tác hệ thống</div>
                                            </span>
                                        </a>
                                    </div>
                                </div>

                                <div class="col-md-1 d-none d-md-flex justify-content-center">
                                    <div class="border-start h-100"></div>
                                </div>

                                {{-- Upload biểu mẫu --}}
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-muted text-uppercase small mb-3" style="letter-spacing:.05em;">Tải lên tệp mẫu in ấn</h6>
                                    <form action="{{ route('settings.upload-asset') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Con dấu băm công ty</label>
                                            <input type="file" name="stamp" class="form-control" accept="image/png,image/jpeg">
                                            <div class="form-text">Dùng trong mẫu Giấy báo nợ. Định dạng PNG/JPG, tối đa 2MB.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold small">Logo công ty</label>
                                            <input type="file" name="logo" class="form-control" accept="image/png,image/jpeg">
                                            <div class="form-text">Hiển thị đầu trang mẫu in ấn. Định dạng PNG/JPG, tối đa 2MB.</div>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6 text-center">
                                                <div class="small text-muted mb-1">Con dấu hiện tại</div>
                                                <img src="{{ asset('img/company-stamp.png') }}?v={{ time() }}" alt="Con dấu"
                                                     class="rounded border bg-light" style="height:60px;object-fit:contain;"
                                                     onerror="this.style.display='none'">
                                            </div>
                                            <div class="col-6 text-center">
                                                <div class="small text-muted mb-1">Logo hiện tại</div>
                                                <img src="{{ asset('img/company-logo.png') }}?v={{ time() }}" alt="Logo"
                                                     class="rounded border bg-light" style="height:60px;object-fit:contain;"
                                                     onerror="this.onerror=null; this.src='{{ asset('img/company-logo.jpg') }}?v={{ time() }}'">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-info fw-bold w-100">
                                            <i class="fa fa-upload me-2"></i>Tải lên tệp đã chọn
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ---------------------------------------------------- --}}
                {{-- TAB 4: LƯU TRỮ DỮ LIỆU (ADMIN only) --}}
                {{-- ---------------------------------------------------- --}}
                <div class="tab-pane fade {{ $activeTab === 'tab-luutru' ? 'show active' : '' }}"
                     id="tab-luutru" role="tabpanel">
                    <div class="card border-0 rounded-4 shadow-sm">
                        <div class="card-header bg-white border-0 p-4 pb-0">
                            <h6 class="fw-bold text-navy text-uppercase mb-1" style="letter-spacing:.05em;">
                                <i class="fa fa-database me-2"></i>Lưu Trữ Dữ Liệu
                            </h6>
                            <p class="text-muted small mb-0">Sao lưu và khôi phục cơ sở dữ liệu hệ thống</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-4">

                                {{-- Sao lưu --}}
                                <div class="col-md-5">
                                    <div class="p-4 bg-success bg-opacity-10 rounded-4 border border-success border-opacity-25 h-100 text-center d-flex flex-column">
                                        <div class="p-3 bg-success text-white rounded-circle d-inline-block mb-3 mx-auto">
                                            <i class="fa fa-download fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-navy">Xuất dữ liệu (Sao lưu)</h6>
                                        <p class="small text-muted mb-4">Tải xuống toàn bộ cấu trúc và dữ liệu của hệ thống dưới dạng SQL.</p>
                                        <a href="{{ route('settings.backup') }}" class="btn btn-success fw-bold w-100 py-2 mb-4">
                                            <i class="fa fa-database me-2"></i>Sao lưu toàn bộ cơ sở dữ liệu
                                        </a>

                                        <hr class="border-success opacity-25 my-0 mb-3">

                                        <h6 class="fw-bold text-navy mb-2"><i class="fa fa-clock-rotate-left me-1"></i>Tự động sao lưu</h6>
                                        <p class="small text-muted mb-3">Hệ thống sẽ tự động sao lưu ngầm theo lịch trình.</p>
                                        <form action="{{ route('settings.update') }}" method="POST" class="mt-auto">
                                            @csrf
                                            <div class="input-group input-group-sm mb-2">
                                                @php
                                                    $autoBackup = \App\Models\Setting::where('key', 'auto_backup_interval')->value('value') ?? 'off';
                                                @endphp
                                                <select name="settings[auto_backup_interval]" class="form-select border-success">
                                                    <option value="off" {{ $autoBackup === 'off' ? 'selected' : '' }}>Không tự động</option>
                                                    <option value="daily" {{ $autoBackup === 'daily' ? 'selected' : '' }}>Mỗi 24 giờ (1 ngày)</option>
                                                    <option value="3_days" {{ $autoBackup === '3_days' ? 'selected' : '' }}>Mỗi 3 ngày</option>
                                                    <option value="weekly" {{ $autoBackup === 'weekly' ? 'selected' : '' }}>Mỗi 7 ngày</option>
                                                    <option value="monthly" {{ $autoBackup === 'monthly' ? 'selected' : '' }}>Mỗi 30 ngày</option>
                                                </select>
                                                <button class="btn btn-success" type="submit">Lưu</button>
                                            </div>
                                            <p class="small text-muted text-center mt-1 mb-0" style="font-size: 0.75rem;">(Bản backup sẽ lưu trong hệ thống máy chủ)</p>
                                        </form>
                                    </div>
                                </div>

                                <div class="col-md-1 d-none d-md-flex justify-content-center align-items-center">
                                    <div class="border-start" style="height:80%;"></div>
                                </div>

                                {{-- Khôi phục --}}
                                <div class="col-md-6">
                                    <div class="p-4 bg-danger bg-opacity-10 rounded-4 border border-danger border-opacity-25 h-100">
                                        <div class="text-center mb-4">
                                            <div class="p-3 bg-danger text-white rounded-circle d-inline-block mb-3">
                                                <i class="fa fa-upload fs-4"></i>
                                            </div>
                                            <h6 class="fw-bold text-navy">Nhập dữ liệu (Khôi phục)</h6>
                                            <p class="small text-muted">
                                                <strong class="text-danger">Cảnh báo:</strong> Thao tác này sẽ ghi đè dữ liệu hiện tại và không thể hoàn tác.
                                            </p>
                                        </div>
                                        <form action="{{ route('settings.restore') }}" method="POST"
                                              enctype="multipart/form-data"
                                              onsubmit="return confirm('XÁC NHẬN KHÔI PHỤC?\n\nThao tác này sẽ ghi đè toàn bộ dữ liệu hiện tại.\nHành động không thể hoàn tác!')">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">Chọn file sao lưu (.sql)</label>
                                                <div class="border border-danger rounded-3 p-3 text-center"
                                                     id="dropZone"
                                                     ondragover="event.preventDefault(); this.classList.add('bg-danger','bg-opacity-10');"
                                                     ondragleave="this.classList.remove('bg-danger','bg-opacity-10');"
                                                     ondrop="handleDrop(event)">
                                                    <i class="fa fa-file-code text-muted fs-3 mb-2 d-block"></i>
                                                    <div class="small text-muted mb-2">Kéo thả file .sql vào đây, hoặc</div>
                                                    <label for="restore_file" class="btn btn-outline-danger btn-sm">Chọn tệp từ máy tính</label>
                                                    <input type="file" name="restore_file" id="restore_file"
                                                           class="d-none" accept=".sql,.txt"
                                                           onchange="updateFileName(this)">
                                                    <div id="fileName" class="small text-success mt-2 fw-semibold"></div>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small">
                                                    <i class="fa fa-lock me-1"></i>Mật khẩu xác nhận cấp cao
                                                </label>
                                                <input type="password" name="admin_password"
                                                       class="form-control @error('admin_password') is-invalid @enderror"
                                                       placeholder="Nhập mật khẩu tài khoản hiện tại" required>
                                                @error('admin_password')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                            <button type="submit" class="btn btn-danger fw-bold w-100 py-2">
                                                <i class="fa fa-exclamation-triangle me-2"></i>Khôi phục hệ thống
                                            </button>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                {{-- ---------------------------------------------------- --}}
                {{-- TAB 5: TRIỂN KHAI (ADMIN only) --}}
                {{-- ---------------------------------------------------- --}}
                <div class="tab-pane fade {{ $activeTab === 'tab-trienkhai' ? 'show active' : '' }}"
                     id="tab-trienkhai" role="tabpanel">
                    <div class="card border-0 rounded-4 shadow-sm">
                        <div class="card-header bg-white border-0 p-4 pb-0">
                            <h6 class="fw-bold text-navy text-uppercase mb-1" style="letter-spacing:.05em;">
                                <i class="fa fa-box me-2"></i>Bộ Cài Đặt Khách Hàng (.exe)
                            </h6>
                            <p class="text-muted small mb-0">Tải xuống file cài đặt All-in-One dùng cho bất kỳ máy tính Windows nào (kể cả không có Internet).</p>
                        </div>
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-6 text-center text-md-start mb-4 mb-md-0">
                                    <div class="mb-4">
                                        <h4 class="fw-bold text-primary mb-2">NT-Logistics-ERP-Setup-v3.0.0.exe</h4>
                                        <span class="badge bg-secondary mb-2">Dung lượng: ~300 MB</span>
                                        <span class="badge bg-success mb-2">Bao gồm: Database + PHP + Tool mạng</span>
                                    </div>
                                    <a href="{{ route('settings.setup-download') }}" class="btn btn-primary btn-lg fw-bold px-5 py-3 shadow-sm rounded-pill">
                                        <i class="fa fa-download me-2"></i>Tải Về Máy Tính
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-info border-0 rounded-4 h-100 m-0">
                                        <h6 class="fw-bold text-info-emphasis mb-3"><i class="fa fa-info-circle me-2"></i>Hướng dẫn cài đặt nhanh:</h6>
                                        <ol class="small text-info-emphasis mb-0 ps-3 lh-lg">
                                            <li>Tải file <strong>.exe</strong> bên cạnh về máy tính mới.</li>
                                            <li>Nhấn đúp (Double-click) để bắt đầu cài đặt.</li>
                                            <li>Làm theo trình hướng dẫn cài đặt (Wizard). Hệ thống sẽ tự động giải nén và cấu hình.</li>
                                            <li>Đợi khoảng 1 phút để ứng dụng tự động cài đặt Database ẩn và nạp dữ liệu nền.</li>
                                            <li>Biểu tượng "Khởi động Server" sẽ xuất hiện trên màn hình Desktop.</li>
                                            <li><strong>Lưu ý:</strong> Quá trình lần đầu tiên cài đặt cần kết nối mạng để đồng bộ thư viện (vendor).</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Modal Hướng dẫn --}}
                <div class="modal fade" id="guideModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content border-0 shadow">
                            <div class="modal-header bg-navy text-white">
                                <h5 class="modal-title fw-bold"><i class="fa fa-book me-2"></i>Hướng Dẫn Gói Triển Khai Portable</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <h6 class="fw-bold text-navy mb-2">Kiến trúc Portable (không cần XAMPP):</h6>
                                <ul class="list-unstyled small text-muted mb-4">
                                    <li class="mb-1"><i class="fa fa-check-circle text-success me-2"></i>Dùng <strong>PHP 8.2 (x86)</strong> và <strong>MariaDB 10.6 (x86)</strong> dạng di động.</li>
                                    <li class="mb-1"><i class="fa fa-check-circle text-success me-2"></i>Hỗ trợ <strong>Windows 32-bit và 64-bit</strong>.</li>
                                    <li class="mb-1"><i class="fa fa-check-circle text-success me-2"></i><strong>Không cần cài XAMPP</strong>, không cần Internet khi cài.</li>
                                    <li class="mb-1"><i class="fa fa-check-circle text-success me-2"></i>MariaDB đăng ký thành <strong>Windows Service</strong> — tự khởi động khi bật máy.</li>
                                </ul>

                                <h6 class="fw-bold text-navy mb-2">Quy trình 3 bước (thực hiện 1 lần):</h6>
                                <ol class="small text-muted ps-3">
                                    <li class="mb-2">Tải file <strong>NT-Logistics-System-Installer.exe</strong> từ tab này về máy tính.</li>
                                    <li class="mb-2">Trên máy mới: <strong>Click đúp</strong> vào file → Hộp thoại hỏi xác nhận cài vào <code>D:\NT-Logistics-ERP</code> → Bấm <strong>OK</strong>.</li>
                                    <li class="mb-2">Cửa sổ đen hiện ra, nhập <strong>tên database</strong> và <strong>thông tin Admin</strong> → Enter → Chờ <strong>2–3 phút</strong> → Trình duyệt tự mở!</li>
                                </ol>

                                <div class="alert alert-warning border-0 rounded-3 small mb-0">
                                    <i class="fa fa-shield-alt me-2"></i>
                                    <strong>Lưu ý:</strong> Cần chạy với quyền <strong>Administrator</strong> để cài Windows Service và mở Firewall. Script sẽ tự yêu cầu nâng quyền nếu chưa có.
                                </div>
                            </div>
                            <div class="modal-footer border-0">
                                <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Đã hiểu</button>
                            </div>
                        </div>
                    </div>
                </div>

                @endif {{-- end isAdmin --}}

            </div>{{-- /tab-content --}}
        </div>{{-- /col-lg-9 --}}

    </div>{{-- /row --}}

</div>
@endsection

@push('scripts')
<script>
    // ── Xác nhận và loading khi Render bộ cài .exe ──────────────────────
    function confirmBuild(form) {
        if (!confirm('Render bo cai .exe moi?\n\nQua trinh mat 30-60 giay, trang se bi freeze trong luc cho.\nBo cai cu (neu co) se bi ghi de.\n\nXac nhan tiep tuc?')) {
            return false;
        }
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Dang Render...';
        return true;
    }

    // Giữ nguyên tab đang chọn sau khi submit form (qua hidden field)
    document.addEventListener('DOMContentLoaded', function () {
        // Nếu URL hash khớp với một tab, kích hoạt tab đó
        const hash = window.location.hash;
        if (hash) {
            const tabTrigger = document.querySelector('[href="' + hash + '"]');
            if (tabTrigger) {
                new bootstrap.Tab(tabTrigger).show();
            }
        }

        // Cập nhật URL hash khi chuyển tab (để F5 vẫn mở đúng tab)
        document.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (el) {
            el.addEventListener('shown.bs.tab', function (e) {
                history.replaceState(null, null, e.target.getAttribute('href'));
            });
        });
    });

    function updateFileName(input) {
        const display = document.getElementById('fileName');
        if (input.files && input.files.length > 0) {
            display.textContent = '✓ Đã chọn: ' + input.files[0].name;
        } else {
            display.textContent = '';
        }
    }

    function handleDrop(event) {
        event.preventDefault();
        document.getElementById('dropZone').classList.remove('bg-danger', 'bg-opacity-10');
        const fileInput = document.getElementById('restore_file');
        if (event.dataTransfer.files.length > 0) {
            fileInput.files = event.dataTransfer.files;
            updateFileName(fileInput);
        }
    }

    // --- Xử lý Đổi Mật Khẩu UI ---
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle ẩn/hiện mật khẩu
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Validation Real-time
        const pwdInput = document.getElementById('new_password');
        const confirmInput = document.getElementById('password_confirmation');
        const reqsBox = document.getElementById('password_requirements');
        
        if (!pwdInput || !confirmInput) return;

        const reqs = {
            length: { el: document.getElementById('req_length'), regex: /.{8,}/ },
            lower: { el: document.getElementById('req_lower'), regex: /[a-z]/ },
            upper: { el: document.getElementById('req_upper'), regex: /[A-Z]/ },
            number: { el: document.getElementById('req_number'), regex: /[0-9]/ },
            special: { el: document.getElementById('req_special'), regex: /[^A-Za-z0-9]/ }
        };
        const matchEl = document.getElementById('req_match');

        function updateRequirement(reqKey, val) {
            const item = reqs[reqKey];
            if (item.regex.test(val)) {
                item.el.classList.add('d-none'); // Ẩn khi đạt yêu cầu
            } else {
                item.el.classList.remove('d-none'); // Hiện khi chưa đạt
            }
        }

        function checkAllRequirements() {
            const val = pwdInput.value;
            if (val.length === 0) {
                reqsBox.classList.add('d-none');
                matchEl.classList.add('d-none');
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

            checkMatch();
        }

        function checkMatch() {
            if (confirmInput.value.length > 0 && pwdInput.value !== confirmInput.value) {
                matchEl.classList.remove('d-none');
                matchEl.classList.remove('text-success');
                matchEl.classList.add('text-danger');
                matchEl.innerHTML = '<i class="fa fa-times-circle me-1"></i> Mật khẩu xác nhận chưa khớp';
            } else if (confirmInput.value.length > 0 && pwdInput.value === confirmInput.value) {
                matchEl.classList.remove('d-none');
                matchEl.classList.remove('text-danger');
                matchEl.classList.add('text-success');
                matchEl.innerHTML = '<i class="fa fa-check-circle me-1"></i> Mật khẩu xác nhận đã khớp';
            } else {
                matchEl.classList.add('d-none');
            }
        }

        pwdInput.addEventListener('input', checkAllRequirements);
        confirmInput.addEventListener('input', checkMatch);

        // --- Xử lý Validation Hồ sơ cá nhân ---
        const profileForm = document.getElementById('profile_form');
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
        }
        
        let emailValidationTimeout;
        if (profileEmail) {
            profileEmail.addEventListener('input', function() {
                clearTimeout(emailValidationTimeout);
                emailValidationTimeout = setTimeout(doValidateEmail, 50);
            });
        }

        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                const isNameValid = doValidateName();
                const isEmailValid = doValidateEmail();
                if (!isNameValid || !isEmailValid) {
                    e.preventDefault();
                }
            });
        }
    });
</script>
@endpush