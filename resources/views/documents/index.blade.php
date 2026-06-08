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

<div class="card border-0 rounded-4 shadow-sm p-4 mb-4">
    <form action="{{ route('documents.index') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Mã chứng từ</label>
            <input type="text" name="document_code" class="form-control border-light" placeholder="Mã chứng từ" value="{{ request('document_code') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Loại chứng từ</label>
            <input type="text" name="doc_category" class="form-control border-light" placeholder="Loại chứng từ" value="{{ request('doc_category') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Mã đơn hàng</label>
            <input type="text" name="job_code" class="form-control border-light" placeholder="Mã đơn hàng" value="{{ request('job_code') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Khách hàng</label>
            <input type="text" name="customer_name" class="form-control border-light" placeholder="Khách hàng" value="{{ request('customer_name') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Luồng chứng từ</label>
            <select name="document_flow" class="form-select border-light">
                <option value="">Tất cả luồng</option>
                <option value="input" {{ request('document_flow') === 'input' ? 'selected' : '' }}>Đầu vào</option>
                <option value="output" {{ request('document_flow') === 'output' ? 'selected' : '' }}>Đầu ra</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Giai đoạn thuế</label>
            <select name="tax_stage" class="form-select border-light">
                <option value="">Tất cả thuế</option>
                <option value="before_tax" {{ request('tax_stage') === 'before_tax' ? 'selected' : '' }}>Trước thuế</option>
                <option value="after_tax" {{ request('tax_stage') === 'after_tax' ? 'selected' : '' }}>Sau thuế</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Người tải</label>
            <input type="text" name="uploader_name" class="form-control border-light" placeholder="Người tải" value="{{ request('uploader_name') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Ngày tải</label>
            <input type="text" name="created_date" class="form-control border-light" placeholder="Ngày/Tháng/Năm" value="{{ \App\Support\VietnameseDate::display(request('created_date')) }}" data-date-input data-label="Ngày tải">
        </div>
        <div class="col-md-2 ms-md-auto">
            <button type="submit" class="btn btn-navy w-100">Lọc</button>
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
                            @if(Auth::user()->hasRole(['ADMIN', 'ACCOUNTANT', 'DOCUMENT']))
                                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#documentEditModal" onclick='prepareDocumentEdit(@json($document))'>
                                    <i class="fa fa-edit"></i>
                                </button>
                                <form action="{{ route('documents.destroy', $document) }}" method="POST" id="delete-form-{{ $document->id }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="handleDelete('{{ $document->id }}', 'Xóa chứng từ {{ $document->document_code }}?')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            @endif
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

<div class="modal fade" id="documentEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="documentEditForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header bg-navy text-white border-0 rounded-top-4 p-4">
                    <h5 class="modal-title fw-bold">Sửa chứng từ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Loại chứng từ <span class="text-danger">*</span></label>
                        <input type="text" name="doc_category" id="edit_doc_category" class="form-control" data-validate required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Luồng chứng từ <span class="text-danger">*</span></label>
                            <select name="document_flow" id="edit_document_flow" class="form-select" data-validate required>
                                <option value="input">Đầu vào</option>
                                <option value="output">Đầu ra</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Giai đoạn thuế <span class="text-danger">*</span></label>
                            <select name="tax_stage" id="edit_tax_stage" class="form-select" data-validate required>
                                <option value="before_tax">Trước thuế</option>
                                <option value="after_tax">Sau thuế</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Thay file mới</label>
                        <input type="file" name="file" class="form-control" accept="image/*,.pdf">
                    </div>
                    <div>
                        <label class="form-label fw-bold small text-muted">Ghi chú</label>
                        <input type="text" name="note" id="edit_note" class="form-control">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-navy fw-bold px-4">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function prepareDocumentEdit(documentData) {
        document.getElementById('documentEditForm').action = `/documents/${documentData.id}`;
        document.getElementById('edit_doc_category').value = documentData.doc_category || '';
        document.getElementById('edit_document_flow').value = documentData.document_flow || 'input';
        document.getElementById('edit_tax_stage').value = documentData.tax_stage || 'before_tax';
        document.getElementById('edit_note').value = documentData.note || '';
    }
</script>
@endpush
@endsection

<x-document-preview-modal />
