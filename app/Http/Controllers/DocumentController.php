<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\FieldAssignment;
use App\Services\ExportService;
use App\Support\VietnameseDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with(['shippingJob.customer', 'uploader'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('document_code', 'like', "%{$search}%")
                        ->orWhere('doc_category', 'like', "%{$search}%")
                        ->orWhereHas('shippingJob', function ($jobQuery) use ($search) {
                            $jobQuery->where('job_code', 'like', "%{$search}%")
                                ->orWhere('container_number', 'like', "%{$search}%");
                        })
                        ->orWhereHas('shippingJob.customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('customer_name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('document_flow'), fn ($query) => $query->where('document_flow', $request->document_flow))
            ->when($request->filled('tax_stage'), fn ($query) => $query->where('tax_stage', $request->tax_stage))
            ->when($request->filled('document_code'), fn ($query) => $query->where('document_code', 'like', "%{$request->input('document_code')}%"))
            ->when($request->filled('doc_category'), fn ($query) => $query->where('doc_category', 'like', "%{$request->input('doc_category')}%"))
            ->when($request->filled('job_code'), fn ($query) => $query->whereHas('shippingJob', fn ($jobQuery) => $jobQuery->where('job_code', 'like', "%{$request->input('job_code')}%")))
            ->when($request->filled('customer_name'), fn ($query) => $query->whereHas('shippingJob.customer', fn ($customerQuery) => $customerQuery->where('customer_name', 'like', "%{$request->input('customer_name')}%")))
            ->when($request->filled('uploader_name'), fn ($query) => $query->whereHas('uploader', fn ($userQuery) => $userQuery->where('name', 'like', "%{$request->input('uploader_name')}%")))
            ->when($request->filled('created_date'), fn ($query) => $query->whereDate('created_at', VietnameseDate::toDatabase($request->input('created_date'))))
            ->latest();

        if ($request->filled('export')) {
            $exportDocuments = (clone $query)
                ->limit(10000)
                ->get();

            return app(ExportService::class)->download((string) $request->string('export'), 'Danh sách chứng từ', 'Tất cả dữ liệu đang lọc', [
                'Mã chứng từ', 'Loại', 'Đơn hàng', 'Khách hàng', 'Luồng', 'Thuế', 'Người tải', 'Ngày tải',
            ], $exportDocuments->map(fn (Document $document): array => [
                $document->document_code,
                $document->doc_category,
                $document->shippingJob?->job_code,
                $document->shippingJob?->customer?->customer_name,
                $document->document_flow,
                $document->tax_stage,
                $document->uploader?->name,
                $document->created_at?->format('d/m/Y'),
            ])->all());
        }

        $documents = $query->paginate(10);

        return view('documents.index', compact('documents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'shipping_job_id' => 'required|exists:shipping_jobs,id',
            'doc_category' => 'required|string|max:100',
            'document_flow' => ['required', Rule::in(['input', 'output'])],
            'tax_stage' => ['required', Rule::in(['before_tax', 'after_tax'])],
            'note' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240', // Max 10MB
        ]);

        if (Auth::user()->hasRole('FIELD')) {
            $fieldStaffId = Auth::user()->fieldStaff?->id;
            $hasActiveAssignment = FieldAssignment::where('shipping_job_id', $request->shipping_job_id)
                ->where('field_staff_id', $fieldStaffId)
                ->whereIn('status', ['assigned', 'completed'])
                ->exists();

            if (! $hasActiveAssignment) {
                return back()->with('error', 'Bạn chưa có phiếu điều hiện trường liên quan đến đơn hàng này.');
            }
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('documents/'.$request->shipping_job_id, 'public');

            Document::create([
                'document_code' => $this->generateDocumentCode($request->doc_category),
                'shipping_job_id' => $request->shipping_job_id,
                'doc_category' => $request->doc_category,
                'document_flow' => $request->document_flow,
                'tax_stage' => $request->tax_stage,
                'file_url' => $path,
                'uploaded_by' => Auth::id(),
                'status' => 'active',
                'note' => $request->note,
            ]);

            return back()->with('success', 'Tải lên chứng từ thành công!');
        }

        return back()->with('error', 'Không tìm thấy file tải lên.');
    }

    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'doc_category' => ['required', 'string', 'max:100'],
            'document_flow' => ['required', Rule::in(['input', 'output'])],
            'tax_stage' => ['required', Rule::in(['before_tax', 'after_tax'])],
            'note' => ['nullable', 'string', 'max:255'],
            'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if ($request->hasFile('file')) {
            if (! $document->isInternalDispatchOrder()) {
                Storage::disk('public')->delete($document->file_url);
            }

            $validated['file_url'] = $request->file('file')->store('documents/'.$document->shipping_job_id, 'public');
        }

        $document->update($validated);

        return back()->with('success', 'Đã cập nhật chứng từ.');
    }

    public function show(Document $document)
    {
        if ($document->isInternalDispatchOrder()) {
            $dispatchOrderId = str_replace('internal://dispatch-order/', '', $document->file_url);

            return redirect()->route('dispatch-orders.show', $dispatchOrderId);
        }

        if (! Storage::disk('public')->exists($document->file_url)) {
            return redirect()->route('shipping-jobs.show', $document->shipping_job_id)
                ->with('error', 'File chứng từ chưa tồn tại trong kho lưu trữ.');
        }

        return Response::file(Storage::disk('public')->path($document->file_url));
    }

    public function destroy($id)
    {
        $document = Document::findOrFail($id);

        if (! $document->isInternalDispatchOrder()) {
            Storage::disk('public')->delete($document->file_url);
        }

        $document->delete();

        return back()->with('success', 'Đã xóa chứng từ.');
    }

    private function generateDocumentCode(string $category): string
    {
        $typeCode = str($category)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9]+/', '-')
            ->trim('-')
            ->substr(0, 12)
            ->value() ?: 'DOC';

        $prefix = "DOC-{$typeCode}-".now()->format('ym').'-';
        $lastDocument = Document::withTrashed()
            ->where('document_code', 'like', "{$prefix}%")
            ->orderBy('document_code', 'desc')
            ->first();

        $lastSequence = $lastDocument ? (int) substr($lastDocument->document_code, -3) : 0;

        return $prefix.str_pad((string) ($lastSequence + 1), 3, '0', STR_PAD_LEFT);
    }
}
