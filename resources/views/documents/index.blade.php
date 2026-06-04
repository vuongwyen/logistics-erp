@extends('layouts.app')

@section('title', 'Quản lý Chứng từ')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Quản lý Chứng từ</h4>
        <p class="text-muted small mb-0">Tra cứu chứng từ đầu vào/đầu ra, trước thuế/sau thuế theo từng đơn hàng.</p>
    </div>
    <x-export-buttons />
</div>

<div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white">
    <form action="{{ route('documents.index') }}" method="GET">
        <div class="row g-3 align-items-center">
            <div class="col-md-2">
                <input type="text" name="document_code" class="form-control border-light" placeholder="Mã chứng từ" value="{{ request('document_code') }}">
            </div>
            <div class="col-md-2">
                <input type="text" name="doc_category" class="form-control border-light" placeholder="Loại chứng từ" value="{{ request('doc_category') }}">
            </div>
            <div class="col-md-2">
                <input type="text" name="job_code" class="form-control border-light" placeholder="Mã đơn hàng" value="{{ request('job_code') }}">
            </div>
            <div class="col-md-2">
                <input type="text" name="customer_name" class="form-control border-light" placeholder="Khách hàng" value="{{ request('customer_name') }}">
            </div>
            <div class="col-md-2">
                <input type="text" name="uploader_name" class="form-control border-light" placeholder="Người tải" value="{{ request('uploader_name') }}">
            </div>
            <div class="col-md-2">
                <select name="document_flow" class="form-select border-light">
                    <option value="">Tất cả luồng</option>
                    <option value="input" {{ request('document_flow') === 'input' ? 'selected' : '' }}>Đầu vào</option>
                    <option value="output" {{ request('document_flow') === 'output' ? 'selected' : '' }}>Đầu ra</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="tax_stage" class="form-select border-light">
                    <option value="">Tất cả thuế</option>
                    <option value="before_tax" {{ request('tax_stage') === 'before_tax' ? 'selected' : '' }}>Trước thuế</option>
                    <option value="after_tax" {{ request('tax_stage') === 'after_tax' ? 'selected' : '' }}>Sau thuế</option>
                </select>
            </div>
            
            <div class="col-md-12 d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('documents.index') }}" class="btn btn-light px-4">Xóa lọc</a>
                <button type="submit" class="btn btn-navy px-4">Tìm kiếm</button>
            </div>
        </div>
    </form>
</div>

<div class="card border-0 rounded-4 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="small text-muted text-uppercase">
                    <th class="ps-4">Chứng từ</th>
                    <th>Đơn hàng</th>
                    <th>Khách hàng</th>
                    <th>Phân loại</th>
                    <th>Người tải</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $document)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-navy">{{ $document->doc_category }}</div>
                            <div class="small text-muted">{{ $document->document_code ?? '---' }}</div>
                            <div class="small text-muted">{{ $document->created_at->format('d/m/Y') }}</div>
                        </td>
                        <td>
                            <a href="{{ route('shipping-jobs.show', $document->shipping_job_id) }}" class="fw-bold text-decoration-none text-navy">
                                {{ $document->shippingJob->job_code }}
                            </a>
                            <div class="small text-muted">{{ $document->shippingJob->container_number ?? '---' }}</div>
                        </td>
                        <td>{{ $document->shippingJob->customer->customer_name }}</td>
                        <td>
                            <span class="badge {{ $document->document_flow === 'input' ? 'bg-primary' : 'bg-success' }}">
                                {{ $document->document_flow === 'input' ? 'Đầu vào' : 'Đầu ra' }}
                            </span>
                            <span class="badge {{ $document->tax_stage === 'before_tax' ? 'bg-warning text-dark' : 'bg-info text-dark' }}">
                                {{ $document->tax_stage === 'before_tax' ? 'Trước thuế' : 'Sau thuế' }}
                            </span>
                            <div class="small text-muted mt-1">{{ $document->note }}</div>
                        </td>
                        <td>{{ $document->uploader->name ?? '---' }}</td>
                        <td class="text-center">
                            @php
                                $fileExt = pathinfo($document->file_url, PATHINFO_EXTENSION);
                                $fileUrl = route('documents.show', $document);
                            @endphp
                            <button type="button" class="btn btn-sm btn-outline-navy" data-bs-toggle="modal" data-bs-target="#previewModal" onclick="previewDocument('{{ $fileUrl }}', '{{ $document->doc_category }}', '{{ $fileExt }}')">
                                <i class="fa fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Chưa có chứng từ phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-top">
        {{ $documents->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection

<x-document-preview-modal />
