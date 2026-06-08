<?php

namespace App\Http\Controllers;

use App\Http\Requests\FieldAssignmentRequest;
use App\Models\FieldAssignment;
use App\Models\FieldStaff;
use App\Models\Location;
use App\Models\ShippingJob;
use App\Models\User;
use App\Notifications\LogisticsNotification;
use App\Services\ExportService;
use App\Support\LogisticsOptions;
use App\Support\VietnameseDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class FieldAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = FieldAssignment::with(['shippingJob.customer', 'fieldStaff', 'location', 'creator'])
            ->latest();

        if ($request->user()->hasRole('FIELD')) {
            $query->where('field_staff_id', $request->user()->fieldStaff?->id ?? 0);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('assignment_code', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhereHas('fieldStaff', fn ($staffQuery) => $staffQuery->where('full_name', 'like', "%{$search}%"))
                    ->orWhereHas('shippingJob', fn ($jobQuery) => $jobQuery->where('job_code', 'like', "%{$search}%")
                        ->orWhere('container_number', 'like', "%{$search}%"))
                    ->orWhereHas('location', fn ($locationQuery) => $locationQuery->where('location_name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        foreach (['assignment_code'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, 'like', "%{$request->input($field)}%");
            }
        }

        if ($request->filled('job_code')) {
            $query->whereHas('shippingJob', fn ($jobQuery) => $jobQuery->where('job_code', 'like', "%{$request->input('job_code')}%"));
        }

        if ($request->filled('field_staff_name')) {
            $query->whereHas('fieldStaff', fn ($staffQuery) => $staffQuery->where('full_name', 'like', "%{$request->input('field_staff_name')}%"));
        }

        if ($request->filled('location_name')) {
            $query->whereHas('location', fn ($locationQuery) => $locationQuery->where('location_name', 'like', "%{$request->input('location_name')}%"));
        }

        if ($request->filled('assigned_date')) {
            $query->whereDate('assigned_date', VietnameseDate::toDatabase($request->input('assigned_date')));
        }

        if ($request->filled('export')) {
            $assignments = $query->limit(10000)->get();

            return app(ExportService::class)->download((string) $request->string('export'), 'Danh sách phiếu điều nhân viên hiện trường', 'Tất cả dữ liệu đang lọc', [
                'Mã phiếu', 'Đơn hàng', 'Nhân viên', 'Vị trí', 'Ngày tạo', 'Người tạo', 'Nhiệm vụ', 'Trạng thái',
            ], $assignments->map(fn (FieldAssignment $assignment): array => [
                $assignment->assignment_code,
                $assignment->shippingJob?->job_code,
                $assignment->fieldStaff?->full_name,
                $assignment->location?->location_name,
                $assignment->assigned_date?->format('d/m/Y'),
                $assignment->creator?->name,
                $this->formatTasks($assignment),
                $this->statusLabel($assignment->status),
            ])->all());
        }

        $fieldAssignments = $query->paginate(10);

        return view('field_assignments.index', compact('fieldAssignments'));
    }

    public function create()
    {
        $shippingJobs = ShippingJob::with('customer')->latest()->limit(100)->get();
        $fieldStaff = FieldStaff::where('status', 'active')->orderBy('full_name')->get();
        $locations = Location::orderBy('location_name')->get();
        $tasks = LogisticsOptions::fieldAssignmentTasks();

        return view('field_assignments.create', compact('shippingJobs', 'fieldStaff', 'locations', 'tasks'));
    }

    public function store(FieldAssignmentRequest $request)
    {
        $fieldAssignment = FieldAssignment::create($request->validated() + [
            'assignment_code' => $this->generateAssignmentCode(),
            'created_by' => auth()->id(),
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        $this->notifyFieldStaff($fieldAssignment, 'Bạn đã nhận được phiếu điều hiện trường '.$fieldAssignment->assignment_code);

        return redirect()->route('field-assignments.index')->with('success', 'Đã tạo phiếu điều nhân viên hiện trường.');
    }

    public function updateStatus(Request $request, FieldAssignment $fieldAssignment)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:assigned,completed,cancelled'],
        ]);

        abort_unless($request->user()->hasRole(['ADMIN', 'DISPATCH']), 403);

        $fieldAssignment->update([
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === 'completed' ? now() : $fieldAssignment->completed_at,
            'cancelled_at' => $validated['status'] === 'cancelled' ? now() : $fieldAssignment->cancelled_at,
        ]);

        if ($validated['status'] === 'cancelled') {
            $this->notifyFieldStaff($fieldAssignment, 'Phiếu điều hiện trường '.$fieldAssignment->assignment_code.' đã bị hủy.');
        }

        return back()->with('success', 'Đã cập nhật trạng thái phiếu hiện trường.');
    }

    private function generateAssignmentCode(): string
    {
        $prefix = 'PHT-'.now()->format('ym').'-';
        $lastAssignment = FieldAssignment::withTrashed()
            ->where('assignment_code', 'like', "{$prefix}%")
            ->orderBy('assignment_code', 'desc')
            ->first();

        $lastSequence = $lastAssignment ? (int) substr($lastAssignment->assignment_code, -3) : 0;

        return $prefix.str_pad((string) ($lastSequence + 1), 3, '0', STR_PAD_LEFT);
    }

    private function notifyFieldStaff(FieldAssignment $fieldAssignment, string $message): void
    {
        $user = $fieldAssignment->fieldStaff?->user;

        if (! $user instanceof User) {
            return;
        }

        Notification::send($user, new LogisticsNotification(
            'Phiếu điều hiện trường',
            $message,
            'fa-user-shield',
            route('field-assignments.index')
        ));
    }

    private function formatTasks(FieldAssignment $fieldAssignment): string
    {
        $taskLabels = LogisticsOptions::fieldAssignmentTasks();

        return collect($fieldAssignment->tasks ?? [])
            ->map(fn (string $task): string => $taskLabels[$task] ?? $task)
            ->implode(', ');
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'assigned' => 'Đã phân công',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Hủy',
            default => 'Mới tạo',
        };
    }
}
