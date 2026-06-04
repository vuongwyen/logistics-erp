@extends('layouts.app')

@section('title', 'Danh mục Khách hàng')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <h4 class="fw-bold mb-0">Danh mục Khách hàng</h4>
    <x-export-buttons />
</div>

<!-- Filters -->
<div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white">
    <form action="{{ route('customers.index') }}" method="GET">
        <div class="row g-3 align-items-center">
            <div class="col-md-2"><input type="text" name="customer_code" class="form-control border-light" placeholder="Mã KH" value="{{ request('customer_code') }}"></div>
            <div class="col-md-2"><input type="text" name="customer_name" class="form-control border-light" placeholder="Tên KH" value="{{ request('customer_name') }}"></div>
            <div class="col-md-2"><input type="text" name="tax_code" class="form-control border-light" placeholder="MST" value="{{ request('tax_code') }}"></div>
            <div class="col-md-2"><input type="text" name="email" class="form-control border-light" placeholder="Email" value="{{ request('email') }}"></div>
            <div class="col-md-2"><input type="text" name="contact_person" class="form-control border-light" placeholder="Người liên hệ" value="{{ request('contact_person') }}"></div>
            <div class="col-md-2"><input type="text" name="phone" class="form-control border-light" placeholder="SĐT" value="{{ request('phone') }}"></div>
            
            <div class="col-md-12 d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('customers.index') }}" class="btn btn-light px-4">Xóa lọc</a>
                <button type="submit" class="btn btn-navy px-4">Tìm kiếm</button>
            </div>
        </div>
    </form>
</div>

<div class="d-flex justify-content-end mb-4">
    <a href="{{ route('customers.create') }}" class="btn btn-navy px-4 fw-bold">
        <i class="fa fa-plus me-2"></i> THÊM KHÁCH HÀNG
    </a>
</div>

<!-- Data Table -->
<div class="card border-0 rounded-4 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="small text-muted text-uppercase">
                    <th class="ps-4">Mã KH</th>
                    <th>Khách Hàng / Công Ty</th>
                    <th>MST</th>
                    <th>Email</th>
                    <th>Người liên hệ</th>
                    <th>Số điện thoại</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td class="ps-4 fw-bold text-navy">{{ $customer->customer_code }}</td>
                        <td>
                            <div class="fw-bold">{{ $customer->customer_name }}</div>
                            <div class="small text-muted">{{ $customer->company_name }}</div>
                        </td>
                        <td><code>{{ $customer->tax_code }}</code></td>
                        <td>{{ $customer->email }}</td>
                        <td>{{ $customer->contact_person ?: '---' }}</td>
                        <td>{{ $customer->phone ?: '---' }}</td>
                        <td class="text-center">
                            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm text-warning me-2">
                                <i class="fa fa-edit"></i>
                            </a>
                            <a href="javascript:void(0)" class="text-danger" title="Xóa" onclick="handleDelete('{{ $customer->id }}', 'Xóa khách hàng {{ $customer->customer_name }}?')">
                                <i class="fa fa-trash"></i>
                            </a>
                            <form id="delete-form-{{ $customer->id }}" action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">Không tìm thấy khách hàng nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="p-4 border-top">
        {{ $customers->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
