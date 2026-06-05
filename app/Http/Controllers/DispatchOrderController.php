<?php

namespace App\Http\Controllers;

use App\Http\Requests\DispatchOrderRequest;
use App\Models\DispatchOrder;
use App\Models\Document;
use App\Models\Driver;
use App\Models\Location;
use App\Models\ShippingJob;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\LogisticsNotification;
use App\Services\DispatchOrderService;
use App\Services\ExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class DispatchOrderController extends Controller
{
    public function __construct(
        protected DispatchOrderService $dispatchOrderService
    ) {}

    public function index(Request $request)
    {
        if ($request->filled('export')) {
            $orders = $this->dispatchOrderService->getAll($request->all(), 10000)->getCollection();

            return app(ExportService::class)->download((string) $request->string('export'), 'Danh sách lệnh điều xe', 'Tất cả dữ liệu đang lọc', [
                'Số lệnh', 'Mã đơn', 'Tài xế', 'Xe', 'Mooc', 'Ngày đi', 'Ngày về', 'Duyệt', 'Trạng thái chuyến',
            ], $orders->map(fn (DispatchOrder $order): array => [
                $order->order_number,
                $order->shippingJob?->job_code,
                $order->driver?->full_name,
                $order->vehicle?->plate_number,
                $order->trailer?->plate_number,
                $order->planned_departure_date?->format('d/m/Y'),
                $order->planned_return_date?->format('d/m/Y'),
                $order->approval_status,
                $order->dispatch_status,
            ])->all());
        }

        $dispatchOrders = $this->dispatchOrderService->getAll($request->all());

        // Lấy danh sách các đơn hàng chưa được điều xe (Pending Jobs)
        $pendingJobs = ShippingJob::whereDoesntHave('dispatchOrders')
            ->with(['customer', 'pickupLocation', 'deliveryLocation'])
            ->latest()
            ->get();

        return view('dispatch_orders.index', compact('dispatchOrders', 'pendingJobs'));
    }

    public function create(Request $request)
    {
        $shippingJobId = $request->query('shipping_job_id');
        $shippingJob = null;

        if ($shippingJobId) {
            $shippingJob = ShippingJob::with(['customer', 'pickupLocation', 'deliveryLocation'])->findOrFail($shippingJobId);
        }

        $vehicles = Vehicle::where('vehicle_type', 'not like', '%Mooc%')->orderBy('plate_number')->get();
        $trailers = Vehicle::where('vehicle_type', 'like', '%Mooc%')->orderBy('plate_number')->get();
        $drivers = Driver::orderBy('full_name')->get();
        $locations = Location::orderBy('location_name')->get();

        return view('dispatch_orders.create', compact('shippingJob', 'vehicles', 'trailers', 'drivers', 'locations'));
    }

    public function store(DispatchOrderRequest $request)
    {
        $this->dispatchOrderService->create($request->validated());

        return redirect()->route('shipping-jobs.show', $request->shipping_job_id)
            ->with('success', 'Đã lập lệnh điều xe và gửi kế toán duyệt.');
    }

    public function show(DispatchOrder $dispatchOrder)
    {
        if (auth()->user()->hasRole('DRIVER')) {
            $driver = auth()->user()->driver;
            if (! $driver || $dispatchOrder->driver_id !== $driver->id) {
                abort(403, 'Bạn không có quyền truy cập lệnh điều xe này.');
            }
        }

        $dispatchOrder->load([
            'shippingJob.customer',
            'shippingJob.pickupLocation',
            'shippingJob.deliveryLocation',
            'vehicle',
            'trailer',
            'driver',
            'startLocation',
            'endLocation',
            'creator',
            'approver',
            'expenses.reporter',
            'cashAdvances.requester',
            'cashAdvances.approver',
        ]);

        return view('dispatch_orders.show', compact('dispatchOrder'));
    }

    public function updateStatus(Request $request, DispatchOrder $dispatchOrder)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:dispatched,on_way,completed'],
            'loading_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'current_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'current_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'actual_fuel_liters' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($dispatchOrder->approval_status !== 'approved') {
            return back()->with('error', 'Lệnh điều xe phải được kế toán duyệt trước khi cập nhật hành trình.');
        }

        if (auth()->user()->hasRole('DRIVER')) {
            $driver = auth()->user()->driver;
            if (! $driver || $dispatchOrder->driver_id !== $driver->id) {
                abort(403, 'Bạn không có quyền cập nhật lệnh điều xe này.');
            }
        }

        $status = $validated['status'] ?? $dispatchOrder->dispatch_status;
        $loadingPercent = $validated['loading_percent'] ?? null;

        if ($loadingPercent === 100) {
            $status = 'completed';
        }

        $updateData = ['dispatch_status' => $status];

        if ($loadingPercent !== null) {
            $updateData['loading_percent'] = $loadingPercent;
        }

        if (array_key_exists('current_latitude', $validated)) {
            $updateData['current_latitude'] = $validated['current_latitude'];
        }

        if (array_key_exists('current_longitude', $validated)) {
            $updateData['current_longitude'] = $validated['current_longitude'];
        }

        if (array_key_exists('actual_fuel_liters', $validated)) {
            $updateData['actual_fuel_liters'] = $validated['actual_fuel_liters'];
        }

        if ($status === 'on_way') {
            $updateData['start_time'] = $dispatchOrder->start_time ?? now();
        } elseif ($status === 'completed') {
            $updateData['end_time'] = $dispatchOrder->end_time ?? now();
            $updateData['loading_percent'] = 100;

            // Cập nhật trạng thái Shipping Job nếu cần
            $dispatchOrder->shippingJob->update(['status' => 'completed']);
        }

        $dispatchOrder->update($updateData);

        // Tạo Nhật ký hành trình
        $dispatchOrder->trackingLogs()->create([
            'status_update' => $status,
            'updated_by' => auth()->id(),
        ]);

        // Notify Admins and Dispatchers
        $statusLabel = match ($status) {
            'on_way' => 'Bắt đầu khởi hành',
            'completed' => 'Đã hoàn thành chuyến xe',
            default => $status
        };

        $notifiableUsers = User::whereHas('role', function ($q) {
            $q->whereIn('role_code', ['ADMIN', 'DISPATCH']);
        })->get();

        $driverName = auth()->user()->name;
        Notification::send($notifiableUsers, new LogisticsNotification(
            'Cập nhật hành trình',
            "Tài xế {$driverName} đã cập nhật trạng thái: {$statusLabel} cho lệnh {$dispatchOrder->order_number}",
            'fa-truck-moving',
            route('dispatch-orders.show', $dispatchOrder)
        ));

        return back()->with('success', 'Đã cập nhật trạng thái chuyến đi thành công!');
    }

    public function approve(DispatchOrder $dispatchOrder)
    {
        $dispatchOrder->update([
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejected_by' => null,
            'rejected_at' => null,
            'rejection_reason' => null,
        ]);

        if ($dispatchOrder->shippingJob->status === 'new') {
            $dispatchOrder->shippingJob->update(['status' => 'dispatched']);
        }

        Document::firstOrCreate(
            [
                'shipping_job_id' => $dispatchOrder->shipping_job_id,
                'file_url' => 'internal://dispatch-order/'.$dispatchOrder->id,
            ],
            [
                'document_code' => $this->generateInternalDocumentCode(),
                'doc_category' => 'Lệnh điều xe',
                'document_flow' => 'output',
                'tax_stage' => 'before_tax',
                'uploaded_by' => auth()->id(),
                'status' => 'active',
                'note' => 'Chứng từ nội bộ tự sinh khi lệnh điều xe được duyệt.',
            ]
        );

        $dispatchOrder->trackingLogs()->create([
            'status_update' => 'approved',
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Đã duyệt lệnh điều xe.');
    }

    public function reject(Request $request, DispatchOrder $dispatchOrder)
    {
        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $dispatchOrder->update([
            'approval_status' => 'rejected',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return back()->with('success', 'Đã từ chối lệnh điều xe.');
    }

    public function destroy(DispatchOrder $dispatchOrder)
    {
        $jobId = $dispatchOrder->shipping_job_id;
        $dispatchOrder->delete();

        return redirect()->route('shipping-jobs.show', $jobId)
            ->with('success', 'Xóa lệnh điều xe thành công!');
    }

    private function generateInternalDocumentCode(): string
    {
        $prefix = 'DOC-NOI-BO-'.now()->format('ym').'-';
        $lastDocument = Document::withTrashed()
            ->where('document_code', 'like', "{$prefix}%")
            ->orderBy('document_code', 'desc')
            ->first();

        $lastSequence = $lastDocument ? (int) substr($lastDocument->document_code, -3) : 0;

        return $prefix.str_pad((string) ($lastSequence + 1), 3, '0', STR_PAD_LEFT);
    }
}
