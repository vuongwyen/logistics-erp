<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleRequest;
use App\Models\Vehicle;
use App\Services\ExportService;
use App\Services\VehicleService;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function __construct(
        protected VehicleService $vehicleService
    ) {}

    public function index(Request $request)
    {
        if ($request->filled('export')) {
            $vehicles = $this->vehicleService->getAll($request->all(), 10000)->getCollection();

            return app(ExportService::class)->download((string) $request->string('export'), 'Danh sách đội xe', 'Tất cả dữ liệu đang lọc', [
                'Biển số', 'Loại xe', 'Tải trọng', 'Hạn đăng kiểm', 'Trạng thái', 'Ghi chú',
            ], $vehicles->map(fn (Vehicle $vehicle): array => [
                $vehicle->plate_number,
                $vehicle->vehicle_type,
                $vehicle->payload,
                $vehicle->registration_expiry?->format('d/m/Y'),
                $vehicle->status,
                $vehicle->note,
            ])->all());
        }

        $vehicles = $this->vehicleService->getAll($request->all());

        return view('vehicles.index', compact('vehicles'));
    }

    public function store(VehicleRequest $request)
    {
        $this->vehicleService->create($request->validated());

        return redirect()->route('vehicles.index')->with('success', 'Thêm xe mới thành công!');
    }

    public function update(VehicleRequest $request, Vehicle $vehicle)
    {
        $this->vehicleService->update($vehicle, $request->validated());

        return redirect()->route('vehicles.index')->with('success', 'Cập nhật thông tin xe thành công!');
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $this->vehicleService->delete($vehicle);

        return redirect()->route('vehicles.index')->with('success', 'Xóa xe thành công!');
    }
}
