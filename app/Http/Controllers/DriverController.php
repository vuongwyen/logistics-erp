<?php

namespace App\Http\Controllers;

use App\Http\Requests\DriverRequest;
use App\Models\Driver;
use App\Models\User;
use App\Services\DriverService;
use App\Services\ExportService;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function __construct(
        protected DriverService $driverService
    ) {}

    public function index(Request $request)
    {
        if ($request->filled('export')) {
            $drivers = $this->driverService->getAll($request->all(), 10000)->getCollection();

            return app(ExportService::class)->download((string) $request->string('export'), 'Danh sách tài xế', 'Tất cả dữ liệu đang lọc', [
                'Mã', 'Họ tên', 'SĐT', 'Ngày sinh', 'GPLX', 'Cấp bậc', 'Ngày vào làm', 'Trạng thái',
            ], $drivers->map(fn (Driver $driver): array => [
                $driver->driver_code,
                $driver->full_name,
                $driver->phone,
                $driver->date_of_birth?->format('d/m/Y'),
                $driver->license_number,
                $driver->rank,
                $driver->start_date?->format('d/m/Y'),
                $driver->status,
            ])->all());
        }

        $drivers = $this->driverService->getAll($request->all());
        $driverUsers = User::query()
            ->whereHas('role', fn ($query) => $query->where('role_code', 'DRIVER'))
            ->whereDoesntHave('driver', fn ($query) => $query->withTrashed())
            ->orderBy('name')
            ->get();

        return view('drivers.index', compact('drivers', 'driverUsers'));
    }

    public function store(DriverRequest $request)
    {
        $this->driverService->create($request->validated());

        return redirect()->route('drivers.index')->with('success', 'Thêm tài xế thành công!');
    }

    public function update(DriverRequest $request, Driver $driver)
    {
        $this->driverService->update($driver, $request->validated());

        return redirect()->route('drivers.index')->with('success', 'Cập nhật thông tin tài xế thành công!');
    }

    public function destroy($id)
    {
        $driver = Driver::findOrFail($id);
        $this->driverService->delete($driver);

        return redirect()->route('drivers.index')->with('success', 'Xóa tài xế thành công!');
    }
}
