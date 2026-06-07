<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShippingJobRequest;
use App\Models\Customer;
use App\Models\Location;
use App\Models\ServicePrice;
use App\Models\ShippingJob;
use App\Services\ExportService;
use App\Services\ShippingJobService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ShippingJobController extends Controller
{
    public function __construct(
        protected ShippingJobService $shippingJobService
    ) {}

    public function index(Request $request)
    {
        if ($request->filled('export')) {
            $jobs = $this->shippingJobService->getAll($request->all(), 10000)->getCollection();

            return app(ExportService::class)->download((string) $request->string('export'), 'Danh sách đơn hàng', 'Tất cả dữ liệu đang lọc', [
                'Mã đơn', 'Khách hàng', 'Số cont', 'Loại cont', 'Hàng hóa', 'Điểm bốc', 'Điểm dỡ', 'Ngày dự kiến', 'Trạng thái',
            ], $jobs->map(fn (ShippingJob $job): array => [
                $job->job_code,
                $job->customer?->customer_name,
                $job->container_number,
                $job->container_type,
                $job->cargo_type,
                $job->pickupLocation?->location_name,
                $job->deliveryLocation?->location_name,
                $job->expected_date ? Carbon::parse($job->expected_date)->format('d/m/Y') : null,
                $job->status,
            ])->all());
        }

        $shippingJobs = $this->shippingJobService->getAll($request->all());
        $customers = Customer::orderBy('customer_name')->get();

        return view('shipping_jobs.index', compact('shippingJobs', 'customers'));
    }

    public function create()
    {
        $customers = Customer::orderBy('customer_name')->get();
        $locations = Location::orderBy('location_name')->get();
        $servicePrices = ServicePrice::orderBy('service_name')->get();

        return view('shipping_jobs.create', compact('customers', 'locations', 'servicePrices'));
    }

    public function store(ShippingJobRequest $request)
    {
        $this->shippingJobService->create($request->validated());

        return redirect()->route('shipping-jobs.index')->with('success', 'Tạo đơn hàng thành công!');
    }

    public function show(ShippingJob $shippingJob)
    {
        if (auth()->user()->hasRole('DRIVER')) {
            $driver = auth()->user()->driver;
            $driverId = $driver ? $driver->id : 0;
            $hasAccess = $shippingJob->dispatchOrders()->where('driver_id', $driverId)->exists();
            if (! $hasAccess) {
                abort(403, 'Bạn không có quyền truy cập đơn hàng này.');
            }
        }

        $shippingJob->load([
            'customer',
            'pickupLocation',
            'deliveryLocation',
            'creator',
            'documents.uploader',
            'expenses.reporter',
            'cashAdvances.requester',
            'debitNote.payments',
            'dispatchOrders.driver',
            'dispatchOrders.vehicle',
            'dispatchOrders.startLocation',
            'dispatchOrders.endLocation',
            'servicePrice',
        ]);

        $serviceFee = $shippingJob->servicePrice ? $shippingJob->servicePrice->unit_price : 0;

        return view('shipping_jobs.show', compact('shippingJob', 'serviceFee'));
    }

    public function edit(ShippingJob $shippingJob)
    {
        $customers = Customer::orderBy('customer_name')->get();
        $locations = Location::orderBy('location_name')->get();
        $servicePrices = ServicePrice::orderBy('service_name')->get();

        return view('shipping_jobs.edit', compact('shippingJob', 'customers', 'locations', 'servicePrices'));
    }

    public function update(ShippingJobRequest $request, ShippingJob $shippingJob)
    {
        $this->shippingJobService->update($shippingJob, $request->validated());

        return redirect()->route('shipping-jobs.index')->with('success', 'Cập nhật đơn hàng thành công!');
    }

    public function destroy(ShippingJob $shippingJob)
    {
        $this->shippingJobService->delete($shippingJob);

        return redirect()->route('shipping-jobs.index')->with('success', 'Xóa đơn hàng thành công!');
    }
}
