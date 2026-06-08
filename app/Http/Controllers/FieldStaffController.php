<?php

namespace App\Http\Controllers;

use App\Http\Requests\FieldStaffRequest;
use App\Models\FieldStaff;
use App\Models\Location;
use App\Models\User;
use App\Services\ExportService;
use App\Services\FieldStaffService;
use Illuminate\Http\Request;

class FieldStaffController extends Controller
{
    public function __construct(
        protected FieldStaffService $fieldStaffService
    ) {}

    public function index(Request $request)
    {
        if ($request->filled('export')) {
            $staff = $this->fieldStaffService->getAll($request->all(), 10000)->getCollection();

            return app(ExportService::class)->download((string) $request->string('export'), 'Danh sách nhân viên hiện trường', 'Tất cả dữ liệu đang lọc', [
                'Mã', 'Họ tên', 'SĐT', 'Ngày sinh', 'Khu vực', 'Chứng chỉ', 'Trạng thái',
            ], $staff->map(fn (FieldStaff $fieldStaff): array => [
                $fieldStaff->staff_code,
                $fieldStaff->full_name,
                $fieldStaff->phone,
                $fieldStaff->date_of_birth?->format('d/m/Y'),
                $fieldStaff->responsibleLocations->pluck('location_name')->implode(', ') ?: $fieldStaff->responsibleLocation?->location_name,
                $fieldStaff->certificates,
                $fieldStaff->status,
            ])->all());
        }

        $fieldStaff = $this->fieldStaffService->getAll($request->all());
        $responsibleLocations = Location::query()
            ->whereIn('type', ['depot', 'warehouse'])
            ->orderBy('location_name')
            ->get();
        $fieldUsers = User::query()
            ->whereHas('role', fn ($query) => $query->where('role_code', 'FIELD'))
            ->whereDoesntHave('fieldStaff', fn ($query) => $query->withTrashed())
            ->orderBy('name')
            ->get();

        return view('field_staff.index', compact('fieldStaff', 'responsibleLocations', 'fieldUsers'));
    }

    public function store(FieldStaffRequest $request)
    {
        $this->fieldStaffService->create($request->validated());

        return redirect()->route('field-staff.index')->with('success', 'Thêm nhân viên hiện trường thành công!');
    }

    public function update(FieldStaffRequest $request, FieldStaff $fieldStaff)
    {
        $this->fieldStaffService->update($fieldStaff, $request->validated());

        return redirect()->route('field-staff.index')->with('success', 'Cập nhật nhân viên hiện trường thành công!');
    }

    public function destroy(FieldStaff $fieldStaff)
    {
        $this->fieldStaffService->delete($fieldStaff);

        return redirect()->route('field-staff.index')->with('success', 'Xóa nhân viên hiện trường thành công!');
    }
}
