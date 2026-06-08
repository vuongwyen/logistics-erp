@extends('layouts.app')

@section('title', 'Phiếu điều nhân viên hiện trường')

@section('content')
@php
    $taskLabels = \App\Support\LogisticsOptions::fieldAssignmentTasks();
    $statusLabels = [
        'new' => 'Mới tạo',
        'assigned' => 'Đã phân công',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Hủy',
    ];
    $statusClasses = [
        'new' => 'bg-secondary',
        'assigned' => 'bg-info text-dark',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger',
    ];
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Phiếu điều nhân viên hiện trường</h4>
        <p class="text-muted small mb-0">Theo dõi phiếu phân công, nhiệm vụ và quyền cập nhật chứng từ theo từng đơn hàng.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if(Auth::user()->hasRole(['ADMIN', 'DISPATCH']))
            <a href="{{ route('field-assignments.create') }}" class="btn btn-navy px-4 fw-bold">
                <i class="fa fa-plus me-2"></i> TẠO PHIẾU
            </a>
        @endif
        <x-export-buttons />
    </div>
</div>

<div class="card border-0 rounded-4 shadow-sm p-4 mb-4">
    <form action="{{ route('field-assignments.index') }}" method="GET" class="row g-3 align-items-end">
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Mã phiếu</label>
            <input type="text" name="assignment_code" class="form-control border-light" placeholder="Mã phiếu" value="{{ request('assignment_code') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Mã đơn hàng</label>
            <input type="text" name="job_code" class="form-control border-light" placeholder="Mã đơn hàng" value="{{ request('job_code') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-bold text-muted">Nhân viên hiện trường</label>
            <input type="text" name="field_staff_name" class="form-control border-light" placeholder="Nhân viên" value="{{ request('field_staff_name') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Vị trí thực hiện</label>
            <input type="text" name="location_name" class="form-control border-light" placeholder="Vị trí" value="{{ request('location_name') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Ngày thực hiện</label>
            <input type="text" name="assigned_date" class="form-control border-light" placeholder="Ngày/Tháng/Năm" value="{{ \App\Support\VietnameseDate::display(request('assigned_date')) }}" data-date-input data-label="Ngày thực hiện">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-bold text-muted">Trạng thái phiếu</label>
            <select name="status" class="form-select border-light">
                <option value="">Trạng thái</option>
                @foreach($statusLabels as $status => $label)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
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
                    <th class="ps-4">Mã phiếu / Ngày tạo</th>
                    <th>Đơn hàng</th>
                    <th>Nhân viên</th>
                    <th>Vị trí thực hiện</th>
                    <th>Nhiệm vụ</th>
                    <th>Trạng thái</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($fieldAssignments as $assignment)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-navy">{{ $assignment->assignment_code }}</div>
                            <div class="small text-muted">{{ $assignment->assigned_date?->format('d/m/Y') ?? $assignment->created_at->format('d/m/Y') }}</div>
                            <div class="small text-muted">Người tạo: {{ $assignment->creator?->name ?? '---' }}</div>
                        </td>
                        <td>
                            @if($assignment->shippingJob)
                                <a href="{{ route('shipping-jobs.show', $assignment->shippingJob) }}" class="fw-bold text-navy text-decoration-none">
                                    {{ $assignment->shippingJob->job_code }}
                                </a>
                                <div class="small text-muted">{{ $assignment->shippingJob->customer?->customer_name }}</div>
                                <div class="small text-muted">Cont: {{ $assignment->shippingJob->container_number ?? '---' }}</div>
                            @else
                                <span class="text-muted">---</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold">{{ $assignment->fieldStaff?->full_name ?? '---' }}</div>
                            <div class="small text-muted">{{ $assignment->fieldStaff?->phone ?? '---' }}</div>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $assignment->location?->location_name ?? '---' }}</div>
                            <div class="small text-muted">{{ $assignment->location?->province ?? '' }}</div>
                        </td>
                        <td class="small" style="min-width: 220px;">
                            @foreach($assignment->tasks ?? [] as $task)
                                <span class="badge bg-light text-dark border mb-1">{{ $taskLabels[$task] ?? $task }}</span>
                            @endforeach
                            @if($assignment->note)
                                <div class="text-muted mt-1">{{ $assignment->note }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $statusClasses[$assignment->status] ?? 'bg-light text-dark' }}">
                                {{ $statusLabels[$assignment->status] ?? $assignment->status }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-2">
                                @if(Auth::user()->hasRole(['ADMIN', 'DISPATCH']) && $assignment->status !== 'completed' && $assignment->status !== 'cancelled')
                                    <form action="{{ route('field-assignments.update-status', $assignment) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Hoàn thành">
                                            <i class="fa fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                                @if(Auth::user()->hasRole(['ADMIN', 'DISPATCH']) && $assignment->status !== 'cancelled')
                                    <form action="{{ route('field-assignments.update-status', $assignment) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hủy phiếu">
                                            <i class="fa fa-ban"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">Chưa có phiếu điều nhân viên hiện trường.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-top">
        {{ $fieldAssignments->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
