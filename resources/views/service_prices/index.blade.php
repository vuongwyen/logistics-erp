@extends('layouts.app')

@section('title', 'Quản lý Biểu giá Dịch vụ')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h4 class="fw-bold mb-0">Quản lý Biểu giá Dịch vụ</h4>
    <x-export-buttons />
</div>

<div class="card border-0 rounded-4 shadow-sm p-4 mb-4">
    <form action="{{ route('service-prices.index') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label small fw-bold text-muted">Mã gói</label>
            <input type="text" name="package_code" class="form-control border-light" placeholder="Mã gói" value="{{ request('package_code') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold text-muted">Tên dịch vụ</label>
            <input type="text" name="service_name" class="form-control border-light" placeholder="Dịch vụ" value="{{ request('service_name') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold text-muted">Đơn vị tính</label>
            <select name="unit" class="form-select border-light">
                <option value="">Đơn vị</option>
                @foreach(\App\Support\LogisticsOptions::serviceUnits() as $value => $label)
                    <option value="{{ $value }}" {{ request('unit') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Thuế</label>
            <select name="is_tax_included" class="form-select border-light">
                <option value="">Tất cả thuế</option>
                <option value="1" {{ request('is_tax_included') === '1' ? 'selected' : '' }}>Đã gồm thuế</option>
                <option value="0" {{ request('is_tax_included') === '0' ? 'selected' : '' }}>Chưa gồm thuế</option>
            </select>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-navy w-100">Lọc</button>
        </div>
    </form>
</div>

<div class="d-flex justify-content-end mb-4">
    <button class="btn btn-navy px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#priceModal" onclick="prepareAdd()">
        <i class="fa fa-plus me-2"></i> THÊM BIỂU GIÁ
    </button>
</div>

<!-- Data Table -->
<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-navy px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#priceModal" onclick="prepareAdd()">
        <i class="fa fa-plus me-2"></i> THÊM BIỂU GIÁ
    </button>
</div>
<div class="card border-0 rounded-4 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="small text-muted text-uppercase">
                    <th class="ps-4">Mã gói</th>
                    <th>Tên Dịch Vụ</th>
                    <th>Đơn Vị Tính</th>
                    <th>Đơn Giá (VNĐ)</th>
                    <th>Thuế</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($servicePrices as $price)
                    <tr>
                        <td class="ps-4 fw-bold text-navy">{{ $price->package_code }}</td>
                        <td class="fw-bold">{{ $price->service_name }}</td>
                        <td>{{ $price->unit }}</td>
                        <td class="fw-bold text-success">{{ number_format($price->unit_price) }}</td>
                        <td>
                            <span class="badge {{ $price->is_tax_included ? 'bg-success' : 'bg-warning text-dark' }}">
                                {{ $price->is_tax_included ? 'Đã gồm thuế' : 'Chưa gồm thuế' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm text-warning me-2" 
                                onclick="prepareEdit({{ json_encode($price) }})"
                                data-bs-toggle="modal" data-bs-target="#priceModal">
                                <i class="fa fa-edit"></i>
                            </button>
                            <a href="javascript:void(0)" class="text-danger" title="Xóa" onclick="handleDelete('{{ $price->id }}', 'Xóa biểu giá {{ $price->service_name }}?')">
                                <i class="fa fa-trash"></i>
                            </a>
                            <form id="delete-form-{{ $price->id }}" action="{{ route('service-prices.destroy', $price->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Chưa có dữ liệu biểu giá.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-top">
        {{ $servicePrices->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- Price Modal -->
<div class="modal fade" id="priceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="priceForm" method="POST">
                @csrf
                <div id="methodField"></div>
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title fw-bold" id="modalTitle">Thêm Biểu Giá Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="row g-3">
                        <div class="col-md-12" id="packageCodeGroup">
                            <label class="form-label fw-semibold">Mã gói</label>
                            <input type="text" id="package_code" class="form-control bg-light border-0" placeholder="Tự sinh khi lưu" disabled>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Tên Dịch Vụ <span class="text-danger">*</span></label>
                            <input type="text" name="service_name" id="service_name" class="form-control bg-light border-0" placeholder="VD: Vận chuyển Cont 20'..." data-validate required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Đơn Vị Tính <span class="text-danger">*</span></label>
                            <select name="unit" id="unit" class="form-select bg-light border-0" data-validate required>
                                @foreach(\App\Support\LogisticsOptions::serviceUnits() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Đơn Giá (VNĐ) <span class="text-danger">*</span></label>
                            <input type="number" name="unit_price" id="unit_price" class="form-control bg-light border-0" data-validate required>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_tax_included" id="is_tax_included" value="1">
                                <label class="form-check-label fw-semibold" for="is_tax_included">Đơn giá đã bao gồm thuế</label>
                            </div>
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
        document.getElementById('modalTitle').innerText = 'Thêm Biểu Giá Mới';
        document.getElementById('priceForm').action = "{{ route('service-prices.store') }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('priceForm').reset();
        document.getElementById('packageCodeGroup').classList.add('d-none');
    }

    function prepareEdit(price) {
        document.getElementById('modalTitle').innerText = 'Chỉnh Sửa Biểu Giá';
        document.getElementById('priceForm').action = `/service-prices/${price.id}`;
        document.getElementById('methodField').innerHTML = '@method("PUT")';
        document.getElementById('packageCodeGroup').classList.remove('d-none');
        
        document.getElementById('package_code').value = price.package_code || '';
        document.getElementById('service_name').value = price.service_name;
        document.getElementById('unit').value = price.unit;
        document.getElementById('unit_price').value = price.unit_price;
        document.getElementById('is_tax_included').checked = !!price.is_tax_included;
    }
</script>
@endpush
@endsection
