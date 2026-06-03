@extends('layouts.app')

@section('title', 'Chi tiết Đơn hàng ' . $shippingJob->job_code)

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <a href="{{ route('shipping-jobs.index') }}" class="text-navy text-decoration-none small fw-bold">
            <i class="fa fa-arrow-left me-1"></i> Quay lại danh sách
        </a>
        <h4 class="fw-bold mt-2">Chi tiết Đơn hàng: {{ $shippingJob->job_code }}</h4>
    </div>
    <div class="d-flex gap-2">
        @if(Auth::user()->hasRole(['ADMIN', 'SALES']))
        <a href="{{ route('shipping-jobs.edit', $shippingJob->id) }}" class="btn btn-warning fw-bold px-4">
            <i class="fa fa-edit me-2"></i> SỬA ĐƠN HÀNG
        </a>
        @endif
        
        @if($shippingJob->status == 'new' && Auth::user()->hasRole(['ADMIN', 'DISPATCH']))
            <a href="{{ route('dispatch-orders.create', ['shipping_job_id' => $shippingJob->id]) }}" class="btn btn-success fw-bold px-4">
                <i class="fa fa-truck me-2"></i> LẬP LỆNH ĐIỀU XE
            </a>
        @endif
    </div>
</div>

<div class="row g-4">
    <!-- Main Info -->
    <div class="col-lg-8">
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-header bg-white border-0 p-4 pb-0">
                <h5 class="fw-bold text-navy mb-0">Thông tin chung</h5>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="small text-muted text-uppercase fw-bold">Khách Hàng</label>
                        <div class="fw-bold text-navy fs-5">{{ $shippingJob->customer->customer_name }}</div>
                        <div class="text-muted">{{ $shippingJob->customer->company_name }}</div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <label class="small text-muted text-uppercase fw-bold">Trạng Thái</label>
                        <div>
                            @php
                                $statusClass = match($shippingJob->status) {
                                    'new' => 'bg-info text-dark',
                                    'processing' => 'bg-primary',
                                    'dispatched' => 'bg-warning text-dark',
                                    'completed' => 'bg-success',
                                    'cancelled' => 'bg-secondary',
                                    default => 'bg-light text-dark'
                                };
                                $statusName = match($shippingJob->status) {
                                    'new' => 'Mới tạo',
                                    'processing' => 'Đang xử lý',
                                    'dispatched' => 'Đã điều xe',
                                    'completed' => 'Hoàn thành',
                                    'cancelled' => 'Đã hủy',
                                    default => 'Khác'
                                };
                            @endphp
                            <span class="badge {{ $statusClass }} fs-6">{{ $statusName }}</span>
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <div class="p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                            <div class="text-center flex-grow-1">
                                <label class="small text-muted text-uppercase d-block mb-1">Nơi Bốc Hàng</label>
                                <span class="fw-bold fs-6 text-navy">{{ $shippingJob->pickupLocation->location_name }}</span>
                                <div class="small text-muted text-truncate">{{ $shippingJob->pickupLocation->address }}</div>
                            </div>
                            <div class="px-4">
                                <i class="fa fa-arrow-right text-muted fs-4"></i>
                            </div>
                            <div class="text-center flex-grow-1">
                                <label class="small text-muted text-uppercase d-block mb-1">Nơi Dỡ Hàng</label>
                                <span class="fw-bold fs-6 text-navy">{{ $shippingJob->deliveryLocation->location_name }}</span>
                                <div class="small text-muted text-truncate">{{ $shippingJob->deliveryLocation->address }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 mt-4">
                        <label class="small text-muted text-uppercase fw-bold">Loại Hàng</label>
                        <div class="fw-bold">{{ $shippingJob->cargo_type }}</div>
                    </div>
                    <div class="col-md-4 mt-4">
                        <label class="small text-muted text-uppercase fw-bold">Container</label>
                        <div class="fw-bold">{{ $shippingJob->container_number ?? 'N/A' }} ({{ $shippingJob->container_type ?? 'Lẻ' }})</div>
                    </div>
                    <div class="col-md-4 mt-4">
                        <label class="small text-muted text-uppercase fw-bold">Hạn Hoàn Thành</label>
                        <div class="fw-bold text-danger">{{ \Carbon\Carbon::parse($shippingJob->expected_date)->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expenses Section -->
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-navy mb-0">Chi phí phát sinh (Chi hộ)</h5>
                @if(Auth::user()->hasRole(['ADMIN', 'ACCOUNTANT', 'DISPATCH', 'FIELD']))
                <button class="btn btn-navy btn-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                    <i class="fa fa-plus-circle me-1"></i> THÊM CHI PHÍ
                </button>
                @endif
            </div>
            <div class="card-body p-4">
                @if($shippingJob->expenses->isEmpty())
                    <div class="text-center py-4 text-muted small">
                        <i class="fa fa-receipt d-block fs-3 mb-2 opacity-50"></i>
                        Chưa có khoản chi phí nào.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr class="small text-muted">
                                    <th>Loại phí</th>
                                    <th>Ghi chú</th>
                                    <th class="text-end">Số tiền</th>
                                    <th>Trạng thái</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($shippingJob->expenses as $expense)
                                    <tr>
                                        <td class="fw-bold small">{{ $expense->expense_type }}</td>
                                        <td class="small text-muted">{{ $expense->note }}</td>
                                        <td class="text-end fw-bold">{{ number_format($expense->amount) }}đ</td>
                                        <td>
                                            <span class="badge {{ $expense->status == 'approved' ? 'bg-success' : 'bg-warning text-dark' }} x-small">
                                                {{ $expense->status == 'approved' ? 'Đã duyệt' : 'Chờ duyệt' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if(Auth::user()->hasRole(['ADMIN', 'ACCOUNTANT']))
                                            <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" id="delete-expense-{{ $expense->id }}" class="d-none">
                                                @csrf @method('DELETE')
                                            </form>
                                            <button type="button" class="btn btn-link text-danger p-0" onclick="handleDelete('delete-expense-{{ $expense->id }}', 'Xóa khoản chi phí này?')">
                                                <i class="fa fa-times-circle"></i>
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Cash Advance Section -->
        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-navy mb-0">Tạm ứng đi đường</h5>
                @if(Auth::user()->hasRole(['ADMIN', 'DISPATCH', 'FIELD']))
                <button class="btn btn-outline-navy btn-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#addAdvanceModal">
                    <i class="fa fa-hand-holding-dollar me-1"></i> YÊU CẦU TẠM ỨNG
                </button>
                @endif
            </div>
            <div class="card-body p-4">
                @if($shippingJob->cashAdvances->isEmpty())
                    <div class="text-center py-3 text-muted small">Chưa có yêu cầu tạm ứng.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr class="small text-muted">
                                    <th>Lý do</th>
                                    <th class="text-end">Số tiền</th>
                                    <th>Trạng thái</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($shippingJob->cashAdvances as $advance)
                                    <tr>
                                        <td class="small">{{ $advance->reason }}</td>
                                        <td class="text-end fw-bold">{{ number_format($advance->amount) }}đ</td>
                                        <td>
                                            <span class="badge {{ $advance->status == 'approved' ? 'bg-success' : ($advance->status == 'rejected' ? 'bg-danger' : 'bg-info text-dark') }} small">
                                                {{ $advance->status == 'approved' ? 'Đã chi' : ($advance->status == 'rejected' ? 'Từ chối' : 'Chờ duyệt') }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if($advance->status == 'pending' && Auth::user()->hasRole(['ADMIN', 'ACCOUNTANT']))
                                                <form action="{{ route('cash-advances.approve', $advance->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success py-0 px-2">Duyệt</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Dispatch Orders Section -->
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white border-0 p-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-navy mb-0">Lệnh điều xe & Hành trình</h5>
                <span class="badge bg-light text-muted fw-normal border">{{ $shippingJob->dispatchOrders->count() }} Lệnh</span>
            </div>
            <div class="card-body p-4">
                @if($shippingJob->dispatchOrders->isEmpty())
                    <div class="text-center py-5">
                        <i class="fa fa-truck-ramp-box fs-1 text-light mb-3"></i>
                        <p class="text-muted">Chưa có lệnh điều xe nào được lập cho đơn hàng này.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr class="small text-muted">
                                    <th>Số Lệnh</th>
                                    <th>Tài Xế / Xe</th>
                                    <th>Trạng Thái</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($shippingJob->dispatchOrders as $order)
                                    <tr>
                                        <td class="fw-bold">{{ $order->order_number }}</td>
                                        <td>
                                            <div class="fw-bold text-navy small">{{ $order->driver->full_name }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">{{ $order->vehicle->plate_number }}</div>
                                        </td>
                                        <td>
                                            @php
                                                $doStatusClass = match($order->dispatch_status) {
                                                    'dispatched' => 'bg-info text-dark',
                                                    'on_way' => 'bg-warning text-dark',
                                                    'completed' => 'bg-success',
                                                    default => 'bg-light text-dark'
                                                };
                                            @endphp
                                            <span class="badge {{ $doStatusClass }} small" style="font-size: 0.7rem;">
                                                {{ $order->dispatch_status == 'dispatched' ? 'Đã điều' : ($order->dispatch_status == 'on_way' ? 'Đang đi' : 'Xong') }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('dispatch-orders.show', $order->id) }}" class="btn btn-sm btn-light">Xem</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        @if(Auth::user()->hasRole(['ADMIN', 'ACCOUNTANT']))
        <!-- Billing Section -->
        <div class="card border-0 rounded-4 shadow-sm mb-4 bg-light">
            <div class="card-body p-4">
                <h6 class="fw-bold text-navy mb-3">Quyết toán & Công nợ</h6>
                @if(!$shippingJob->debitNote)
                    <div class="text-center py-3">
                        <p class="small text-muted mb-3">Đơn hàng này chưa được lập Giấy báo nợ (Debit Note).</p>
                        <form action="{{ route('debit-notes.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="shipping_job_id" value="{{ $shippingJob->id }}">
                            <button type="submit" class="btn btn-navy w-100 fw-bold">
                                <i class="fa fa-file-invoice-dollar me-2"></i> LẬP GIẤY BÁO NỢ
                            </button>
                        </form>
                    </div>
                @else
                    <div class="p-3 bg-white rounded-3 shadow-sm mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Số báo nợ:</span>
                            <span class="fw-bold">{{ $shippingJob->debitNote->note_number }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small text-muted">Tổng tiền:</span>
                            <span class="fw-bold text-danger">{{ number_format($shippingJob->debitNote->grand_total) }}đ</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="small text-muted">Trạng thái:</span>
                            @php
                                $dnStatus = match($shippingJob->debitNote->status) {
                                    'paid' => ['bg-success', 'Đã tất toán'],
                                    'partial' => ['bg-warning text-dark', 'Đã trả một phần'],
                                    default => ['bg-danger', 'Chưa thu tiền']
                                };
                            @endphp
                            <span class="badge {{ $dnStatus[0] }}">{{ $dnStatus[1] }}</span>
                        </div>
                        <div class="d-grid gap-2">
                            <div class="d-flex gap-1">
                                <a href="{{ route('debit-notes.show', $shippingJob->debitNote->id) }}" class="btn btn-sm btn-outline-navy flex-grow-1">
                                    <i class="fa fa-eye me-1"></i> Xem & In
                                </a>
                                @if(Auth::user()->hasRole(['ADMIN', 'ACCOUNTANT']))
                                <form action="{{ route('debit-notes.store') }}" method="POST" class="flex-shrink-0">
                                    @csrf
                                    <input type="hidden" name="shipping_job_id" value="{{ $shippingJob->id }}">
                                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Cập nhật số liệu mới nhất">
                                        <i class="fa fa-sync"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                            @if($shippingJob->debitNote->status != 'paid' && Auth::user()->hasRole(['ADMIN', 'ACCOUNTANT']))
                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                    <i class="fa fa-money-bill me-1"></i> Ghi nhận Thanh toán
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <div class="card border-0 rounded-4 shadow-sm mb-4">
            <div class="card-body p-4">
                <h6 class="fw-bold text-navy mb-3">Thông tin bổ sung</h6>
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <label class="small text-muted d-block">Tờ khai hải quan:</label>
                        <span class="fw-bold">{{ $shippingJob->customs_declaration_no ?? 'Chưa cập nhật' }}</span>
                    </li>
                    <li class="mb-3">
                        <label class="small text-muted d-block">Người tạo:</label>
                        <span class="fw-bold">{{ $shippingJob->creator->name }}</span>
                    </li>
                    <li>
                        <label class="small text-muted d-block">Thời gian tạo:</label>
                        <span class="fw-bold">{{ $shippingJob->created_at->format('d/m/Y') }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card border-0 rounded-4 shadow-sm bg-navy text-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Hồ sơ chứng từ</h6>
                    @if(Auth::user()->hasRole(['ADMIN', 'DOCUMENT', 'DISPATCH', 'FIELD']))
                    <button class="btn btn-sm btn-outline-light border-0 p-1" data-bs-toggle="modal" data-bs-target="#uploadDocModal">
                        <i class="fa fa-upload"></i>
                    </button>
                    @endif
                </div>
                
                @if($shippingJob->documents->isEmpty())
                    <div class="text-center py-3">
                        <i class="fa fa-folder-open fs-2 opacity-50 mb-2"></i>
                        <p class="small opacity-75 mb-0">Chưa có chứng từ</p>
                    </div>
                @else
                    <div class="list-group list-group-flush bg-transparent">
                        @foreach($shippingJob->documents as $doc)
                            <div class="list-group-item bg-transparent text-white border-white border-opacity-10 px-0 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center overflow-hidden">
                                    <i class="fa {{ Str::endsWith($doc->file_url, '.pdf') ? 'fa-file-pdf' : 'fa-file-image' }} me-2 opacity-75"></i>
                                    <div class="text-truncate">
                                        <div class="small fw-bold">{{ $doc->doc_category }}</div>
                                        <div class="small opacity-50" style="font-size: 0.7rem;">{{ $doc->created_at->format('d/m H:i') }}</div>
                                    </div>
                                </div>
                                <div class="d-flex gap-1">
                                    @php
                                        $fileExt = pathinfo($doc->file_url, PATHINFO_EXTENSION);
                                        $fileUrl = route('documents.show', $doc);
                                    @endphp
                                    <button type="button" class="btn btn-link text-white p-1" data-bs-toggle="modal" data-bs-target="#previewModal" onclick="previewDocument('{{ $fileUrl }}', '{{ $doc->doc_category }}', '{{ $fileExt }}')">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <form action="{{ route('documents.destroy', $doc->id) }}" method="POST" id="delete-doc-{{ $doc->id }}" class="d-none">
                                        @csrf @method('DELETE')
                                    </form>
                                    <button type="button" class="btn btn-link text-danger p-1" onclick="handleDelete('delete-doc-{{ $doc->id }}', 'Xóa chứng từ này?')">
                                        <i class="fa fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Thêm Chi Phí -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-navy text-white border-0 rounded-top-4 p-4">
                <h5 class="modal-title fw-bold">Ghi nhận chi phí mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf
                <input type="hidden" name="shipping_job_id" value="{{ $shippingJob->id }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Loại chi phí</label>
                        <select name="expense_type" class="form-select" required>
                            <option value="Cầu đường (BOT)">Cầu đường (BOT)</option>
                            <option value="Nhiên liệu (Dầu)">Nhiên liệu (Dầu)</option>
                            <option value="Bồi dưỡng / Cafe">Bồi dưỡng / Cafe</option>
                            <option value="Lưu ca / Chờ đợi">Lưu ca / Chờ đợi</option>
                            <option value="Sửa chữa dọc đường">Sửa chữa dọc đường</option>
                            <option value="Phí bến bãi">Phí bến bãi</option>
                            <option value="Khác">Khác...</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Số tiền (VNĐ)</label>
                        <input type="number" name="amount" class="form-control" placeholder="0" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="VD: Trạm thu phí Long Thành..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-navy fw-bold px-4">LƯU CHI PHÍ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tạm Ứng -->
<div class="modal fade" id="addAdvanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-navy text-white border-0 rounded-top-4 p-4">
                <h5 class="modal-title fw-bold">Yêu cầu tạm ứng</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('cash-advances.store') }}" method="POST">
                @csrf
                <input type="hidden" name="shipping_job_id" value="{{ $shippingJob->id }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Số tiền tạm ứng (VNĐ)</label>
                        <input type="number" name="amount" class="form-control" placeholder="VD: 500000" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted">Lý do / Nội dung chi</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="VD: Tạm ứng tiền dầu và phí cầu đường đi Hải Phòng..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-navy fw-bold px-4">GỬI YÊU CẦU</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Thanh Toán -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-success text-white border-0 rounded-top-4 p-4">
                <h5 class="modal-title fw-bold">Ghi nhận thanh toán</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="debit_note_id" value="{{ $shippingJob->debitNote->id ?? '' }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Số tiền khách trả (VNĐ)</label>
                        <input type="number" name="amount" class="form-control" value="{{ $shippingJob->debitNote->grand_total ?? 0 }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Phương thức</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="Chuyển khoản">Chuyển khoản (UNC)</option>
                            <option value="Tiền mặt">Tiền mặt</option>
                            <option value="Cấn trừ nợ">Cấn trừ công nợ</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Số chứng từ (Nếu có)</label>
                        <input type="text" name="reference_no" class="form-control" placeholder="VD: UNC-00123">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted">Ghi chú</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">XÁC NHẬN THANH TOÁN</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tải lên Chứng từ -->
<div class="modal fade" id="uploadDocModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header bg-navy text-white border-0 rounded-top-4 p-4">
                <h5 class="modal-title fw-bold">Tải lên chứng từ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="shipping_job_id" value="{{ $shippingJob->id }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Loại chứng từ</label>
                        <select name="doc_category" class="form-select" required>
                            <option value="Phiếu giao hàng (POD)">Phiếu giao hàng (POD)</option>
                            <option value="Lệnh giao hàng (D/O)">Lệnh giao hàng (D/O)</option>
                            <option value="Phiếu hạ bãi (EIR)">Phiếu hạ bãi (EIR)</option>
                            <option value="Tờ khai Hải quan">Tờ khai Hải quan</option>
                            <option value="Hóa đơn (Invoice)">Hóa đơn (Invoice)</option>
                            <option value="Khác">Khác...</option>
                        </select>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Luồng chứng từ</label>
                            <select name="document_flow" class="form-select" required>
                                <option value="input">Đầu vào</option>
                                <option value="output">Đầu ra</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Giai đoạn thuế</label>
                            <select name="tax_stage" class="form-select" required>
                                <option value="before_tax">Trước thuế</option>
                                <option value="after_tax">Sau thuế</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold small text-muted">Chọn file (Ảnh hoặc PDF)</label>
                        <input type="file" name="file" class="form-control" accept="image/*,.pdf" required>
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-bold small text-muted">Ghi chú</label>
                        <input type="text" name="note" class="form-control" placeholder="VD: Hóa đơn sau thuế bản scan">
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-navy fw-bold px-4">TẢI LÊN</button>
                </div>
            </form>
        </div>
    </div>
</div>
    </div>
</div>
@endsection

<x-document-preview-modal />
