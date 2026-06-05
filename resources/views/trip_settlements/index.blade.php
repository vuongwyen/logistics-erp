@extends('layouts.app')

@section('title', 'Quyết toán chuyến đi')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold mb-1">Quyết toán chuyến đi</h4>
        <p class="text-muted small mb-0">Quản lý các bản quyết toán chi phí và tạm ứng theo từng lệnh điều vận.</p>
    </div>
    <button class="btn btn-navy px-4 fw-bold" data-bs-toggle="modal" data-bs-target="#tripSettlementModal" onclick="prepareAdd()">
        <i class="fa fa-plus me-2"></i> TẠO QUYẾT TOÁN MỚI
    </button>
</div>

<div class="card border-0 rounded-4 shadow-sm p-3 mb-4 bg-white">
    <form action="{{ route('trip-settlements.index') }}" method="GET">
        <div class="row g-3 align-items-center">
            <div class="col-md-3">
                <input type="text" name="settlement_code" class="form-control border-light" placeholder="Mã quyết toán" value="{{ request('settlement_code') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select border-light">
                    <option value="">Tất cả trạng thái</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Nháp</option>
                    <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Đã nộp (Chờ duyệt)</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Từ chối</option>
                </select>
            </div>
            
            <div class="col-md-12 d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('trip-settlements.index') }}" class="btn btn-light px-4">Xóa lọc</a>
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
                    <th class="ps-4">Mã Quyết Toán</th>
                    <th>Lệnh Điều Vận</th>
                    <th>Tài Xế / Xe</th>
                    <th class="text-end">Tổng Tạm Ứng</th>
                    <th class="text-end">Tổng Chi Phí</th>
                    <th class="text-end">Số Dư</th>
                    <th>Trạng Thái</th>
                    <th>Người Lập/Duyệt</th>
                    <th class="text-center">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($settlements as $settlement)
                    <tr>
                        <td class="ps-4 fw-medium text-navy">{{ $settlement->settlement_code }}</td>
                        <td>{{ $settlement->dispatchOrder->order_number ?? 'N/A' }}</td>
                        <td>
                            <div class="fw-medium">{{ $settlement->dispatchOrder->driver->full_name ?? 'N/A' }}</div>
                            <div class="small text-muted">{{ $settlement->dispatchOrder->vehicle->plate_number ?? 'N/A' }}</div>
                        </td>
                        <td class="text-end text-primary fw-medium">{{ number_format($settlement->total_cash_advance) }}</td>
                        <td class="text-end text-danger fw-medium">{{ number_format($settlement->total_expense) }}</td>
                        <td class="text-end fw-bold {{ $settlement->balance >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ number_format($settlement->balance) }}
                        </td>
                        <td>
                            @if($settlement->status === 'draft')
                                <span class="badge bg-secondary">Nháp</span>
                            @elseif($settlement->status === 'submitted')
                                <span class="badge bg-warning text-dark">Đang chờ duyệt</span>
                            @elseif($settlement->status === 'approved')
                                <span class="badge bg-success">Đã duyệt</span>
                            @elseif($settlement->status === 'rejected')
                                <span class="badge bg-danger">Từ chối</span>
                            @endif
                        </td>
                        <td>
                            <div class="small">Lập: {{ $settlement->requester->name ?? 'N/A' }}</div>
                            @if($settlement->approver)
                                <div class="small text-muted">Duyệt: {{ $settlement->approver->name }}</div>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                @if($settlement->status === 'submitted')
                                    @if(Auth::user()->hasRole(['ADMIN', 'ACCOUNTANT']))
                                    <form action="{{ route('trip-settlements.approve', $settlement) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Duyệt"><i class="fa fa-check"></i></button>
                                    </form>
                                    <button class="btn btn-sm btn-outline-danger" title="Từ chối" onclick="rejectSettlement({{ $settlement->id }})"><i class="fa fa-times"></i></button>
                                    @endif
                                @endif
                                
                                @if($settlement->status !== 'approved')
                                <button class="btn btn-sm btn-outline-secondary" onclick='editSettlement(@json($settlement))' title="Sửa Ghi chú">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <form action="{{ route('trip-settlements.destroy', $settlement) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa quyết toán này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa"><i class="fa fa-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">Chưa có quyết toán nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-top">
        {{ $settlements->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- Modal Create/Edit -->
<div class="modal fade" id="tripSettlementModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="tripSettlementForm" method="POST" action="{{ route('trip-settlements.store') }}">
                @csrf
                <div id="methodField"></div>
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title fw-bold" id="modalTitle">Tạo Quyết Toán Mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="row g-3">
                        <div class="col-md-12" id="dispatchOrderSelectWrapper">
                            <label class="form-label fw-semibold">Lệnh Điều Vận (Chưa quyết toán)</label>
                            <select name="dispatch_order_id" id="dispatch_order_id" class="form-select bg-light border-0" required onchange="fetchFinancials(this.value)">
                                <option value="">-- Chọn lệnh điều vận --</option>
                                @foreach($dispatchOrders as $order)
                                    <option value="{{ $order->id }}">{{ $order->order_number }} - Tài xế: {{ $order->driver->full_name ?? '' }} - Xe: {{ $order->vehicle->plate_number ?? '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-primary">Tổng Tạm Ứng</label>
                            <input type="text" id="disp_total_advance" class="form-control bg-light border-0 fw-bold text-primary" readonly value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-danger">Tổng Chi Phí</label>
                            <input type="text" id="disp_total_expense" class="form-control bg-light border-0 fw-bold text-danger" readonly value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Số Dư <small>(Dương: hoàn lại, Âm: chi thêm)</small></label>
                            <input type="text" id="disp_balance" class="form-control bg-light border-0 fw-bold" readonly value="0">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Ghi chú</label>
                            <textarea name="note" id="note" class="form-control bg-light border-0" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-navy px-4">Lưu Quyết Toán</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-4 shadow">
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title fw-bold text-danger">Từ Chối Quyết Toán</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-0">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control bg-light border-0" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger px-4">Xác nhận Từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function prepareAdd() {
        document.getElementById('tripSettlementForm').reset();
        document.getElementById('tripSettlementForm').action = "{{ route('trip-settlements.store') }}";
        document.getElementById('methodField').innerHTML = '';
        document.getElementById('modalTitle').innerText = 'Tạo Quyết Toán Mới';
        document.getElementById('dispatchOrderSelectWrapper').style.display = 'block';
        document.getElementById('dispatch_order_id').setAttribute('required', 'required');
        
        document.getElementById('disp_total_advance').value = '0';
        document.getElementById('disp_total_expense').value = '0';
        document.getElementById('disp_balance').value = '0';
        document.getElementById('disp_balance').className = 'form-control bg-light border-0 fw-bold';
    }

    function editSettlement(settlement) {
        prepareAdd();
        document.getElementById('tripSettlementForm').action = `/trip-settlements/${settlement.id}`;
        document.getElementById('methodField').innerHTML = '@method("PUT")';
        document.getElementById('modalTitle').innerText = 'Cập Nhật Quyết Toán: ' + settlement.settlement_code;
        
        // Hide order selection
        document.getElementById('dispatchOrderSelectWrapper').style.display = 'none';
        document.getElementById('dispatch_order_id').removeAttribute('required');
        
        // Set financials statically
        document.getElementById('disp_total_advance').value = new Intl.NumberFormat().format(settlement.total_cash_advance);
        document.getElementById('disp_total_expense').value = new Intl.NumberFormat().format(settlement.total_expense);
        
        const bal = parseFloat(settlement.balance);
        document.getElementById('disp_balance').value = new Intl.NumberFormat().format(bal);
        if (bal >= 0) {
            document.getElementById('disp_balance').classList.add('text-success');
            document.getElementById('disp_balance').classList.remove('text-danger');
        } else {
            document.getElementById('disp_balance').classList.add('text-danger');
            document.getElementById('disp_balance').classList.remove('text-success');
        }

        document.getElementById('note').value = settlement.note || '';
        
        new bootstrap.Modal(document.getElementById('tripSettlementModal')).show();
    }

    function rejectSettlement(id) {
        document.getElementById('rejectForm').action = `/trip-settlements/${id}/reject`;
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }

    async function fetchFinancials(dispatchOrderId) {
        if (!dispatchOrderId) {
            document.getElementById('disp_total_advance').value = '0';
            document.getElementById('disp_total_expense').value = '0';
            document.getElementById('disp_balance').value = '0';
            return;
        }

        try {
            const response = await fetch(`/trip-settlements/dispatch-order/${dispatchOrderId}/financials`);
            const data = await response.json();

            if (response.ok) {
                document.getElementById('disp_total_advance').value = new Intl.NumberFormat().format(data.total_cash_advance);
                document.getElementById('disp_total_expense').value = new Intl.NumberFormat().format(data.total_expense);
                
                const bal = parseFloat(data.balance);
                document.getElementById('disp_balance').value = new Intl.NumberFormat().format(bal);
                
                if (bal >= 0) {
                    document.getElementById('disp_balance').classList.add('text-success');
                    document.getElementById('disp_balance').classList.remove('text-danger');
                } else {
                    document.getElementById('disp_balance').classList.add('text-danger');
                    document.getElementById('disp_balance').classList.remove('text-success');
                }
            } else {
                alert(data.error || 'Đã có lỗi xảy ra khi lấy số liệu.');
            }
        } catch (error) {
            console.error('Error fetching financials:', error);
            alert('Lỗi kết nối khi lấy số liệu quyết toán.');
        }
    }
</script>
@endpush
