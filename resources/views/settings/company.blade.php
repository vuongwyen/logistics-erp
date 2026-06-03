@extends('layouts.app')

@section('title', 'Thông tin công ty - NT Logistics')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold text-navy"><i class="fa fa-building me-2"></i>Thông tin công ty</h4>
            <p class="text-muted small mb-0">Cập nhật thông tin doanh nghiệp, logo và biểu mẫu in ấn.</p>
        </div>
    </div>

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
        <div class="col-lg-7">
            <div class="card border-0 rounded-4 shadow-sm h-100 p-3 mb-4 bg-white">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h6 class="fw-bold text-navy text-uppercase mb-1" style="letter-spacing:.05em;">
                        <i class="fa fa-info-circle me-2"></i>Thông tin chung
                    </h6>
                    <p class="text-muted small mb-0">Thông tin này sẽ được sử dụng trên các báo cáo và chứng từ.</p>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('settings.update') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Tên công ty <span class="text-danger">*</span></label>
                                <input type="text" name="settings[company.name]" id="company_name" class="form-control" value="{{ $companySettings['company.name']->value ?? 'CÔNG TY TNHH NT LOGISTICS' }}" required oninput="formatCompanyName(this)">
                                <div class="invalid-feedback" id="company_name_error" style="display: none;"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small text-muted">Địa chỉ</label>
                                <input type="text" name="settings[company.address]" class="form-control" value="{{ $companySettings['company.address']->value ?? '123 Đường Số 1, Phường 2, Quận 3, TP. Hồ Chí Minh' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Mã số thuế</label>
                                <input type="text" name="settings[company.tax_code]" class="form-control" value="{{ $companySettings['company.tax_code']->value ?? '0312345678' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Số điện thoại</label>
                                <input type="text" name="settings[company.phone]" class="form-control" value="{{ $companySettings['company.phone']->value ?? '028 3838 8888' }}" oninput="formatPhone(this)">
                                <div class="invalid-feedback" id="phone_error" style="display: none;"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted">Email</label>
                                <input type="email" name="settings[company.email]" class="form-control" value="{{ $companySettings['company.email']->value ?? 'info@ntlogistics.vn' }}">
                            </div>
                        </div>
                        <div class="mt-4 pt-2">
                            <button type="submit" class="btn btn-navy fw-bold px-4">
                                <i class="fa fa-save me-2"></i>Lưu thông tin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 rounded-4 shadow-sm h-100 p-3 mb-4 bg-white">
                <div class="card-header bg-white border-0 p-4 pb-0">
                    <h6 class="fw-bold text-navy text-uppercase mb-1" style="letter-spacing:.05em;">
                        <i class="fa fa-image me-2"></i>Tải lên tệp mẫu in ấn
                    </h6>
                    <p class="text-muted small mb-0">Con dấu băm và logo sẽ xuất hiện trên các mẫu in ấn hóa đơn, chứng từ.</p>
                </div>
                <div class="card-body p-4">
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
                        <div class="row g-2 mb-4">
                            <div class="col-6 text-center">
                                <div class="small text-muted mb-1">Con dấu hiện tại</div>
                                <img src="{{ asset('img/company-stamp.png') }}?v={{ time() }}" alt="Con dấu"
                                        class="rounded border bg-light w-100 p-2" style="height:80px;object-fit:contain;"
                                        onerror="this.style.display='none'">
                            </div>
                            <div class="col-6 text-center">
                                <div class="small text-muted mb-1">Logo hiện tại</div>
                                <img src="{{ asset('img/company-logo.png') }}?v={{ time() }}" alt="Logo"
                                        class="rounded border bg-light w-100 p-2" style="height:80px;object-fit:contain;"
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
@push('scripts')
<script>
    function formatCompanyName(input) {
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

        if (/[^a-zA-Z0-9ÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂưăạảấầẩẫậắằẳẵặẹẻẽềềểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễệỉịọỏốồổỗộớờởỡợụủứừỮỰỲỴÝỶỸửữựỳỵỷỹ\s\.\,\-\&]/g.test(val)) {
            errorMsg = "Tên công ty không được chứa ký tự đặc biệt.";
        }
        val = val.replace(/[^a-zA-Z0-9ÀÁÂÃÈÉÊÌÍÒÓÔÕÙÚĂĐĨŨƠàáâãèéêìíòóôõùúăđĩũơƯĂẠẢẤẦẨẪẬẮẰẲẴẶẸẺẼỀỀỂưăạảấầẩẫậắằẳẵặẹẻẽềềểỄỆỈỊỌỎỐỒỔỖỘỚỜỞỠỢỤỦỨỪễệỉịọỏốồổỗộớờởỡợụủứừỮỰỲỴÝỶỸửữựỳỵỷỹ\s\.\,\-\&]/g, '');
        
        val = val.replace(/(?:^|\s)\S/g, function(a) { return a.toUpperCase(); });
        
        input.value = val;
        
        let errorDiv = document.getElementById('company_name_error');
        if (errorMsg !== '') {
            input.classList.add('is-invalid');
            errorDiv.innerText = errorMsg;
            errorDiv.style.display = 'block';
        } else {
            input.classList.remove('is-invalid');
            errorDiv.style.display = 'none';
        }
    }

    function formatPhone(input) {
        let val = input.value;
        let errorMsg = '';

        if (/[^0-9\s\.\-\+]/g.test(val)) {
            errorMsg = "Số điện thoại chỉ được chứa số, khoảng trắng, +, -, .";
        }
        val = val.replace(/[^0-9\s\.\-\+]/g, '');
        
        input.value = val;
        
        let errorDiv = document.getElementById('phone_error');
        if (errorDiv) {
            if (errorMsg !== '') {
                input.classList.add('is-invalid');
                errorDiv.innerText = errorMsg;
                errorDiv.style.display = 'block';
            } else {
                input.classList.remove('is-invalid');
                errorDiv.style.display = 'none';
            }
        }
    }
</script>
@endpush
@endsection
