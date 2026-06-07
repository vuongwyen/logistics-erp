<?php

namespace App\Http\Controllers;

use App\Models\DebitNote;
use App\Models\ShippingJob;
use Illuminate\Http\Request;

class DebitNoteController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'shipping_job_id' => 'required|exists:shipping_jobs,id',
        ]);

        $job = ShippingJob::with(['expenses'])->findOrFail($request->shipping_job_id);

        // Lấy giá dịch vụ đã chọn, nếu không thì lấy 0
        $serviceFee = $job->servicePrice ? $job->servicePrice->unit_price : 0;

        $totalExpenses = $job->expenses->where('status', 'approved')->sum('amount');

        $debitNote = DebitNote::firstOrNew(['shipping_job_id' => $job->id]);
        $wasRecentlyCreated = ! $debitNote->exists;

        if ($wasRecentlyCreated) {
            $debitNote->note_number = $this->generateNoteNumber();
            $debitNote->status = 'unpaid';
        }

        $debitNote->fill([
            'customer_id' => $job->customer_id,
            'total_service_fee' => $serviceFee,
            'total_expense_paid' => $totalExpenses,
            'grand_total' => $serviceFee + $totalExpenses,
            'issued_at' => $debitNote->issued_at ?? now(),
        ]);
        $debitNote->save();

        if (! $wasRecentlyCreated) {
            return back()->with('success', 'Đã cập nhật lại Giấy báo nợ với dữ liệu chi phí mới nhất!');
        }

        return back()->with('success', 'Đã lập Giấy báo nợ thành công!');
    }

    public function show(DebitNote $debitNote)
    {
        $debitNote->load([
            'customer',
            'payments.receiver',
            'shippingJob.customer',
            'shippingJob.pickupLocation',
            'shippingJob.deliveryLocation',
            'shippingJob.expenses',
        ]);

        return view('debit_notes.show', compact('debitNote'));
    }

    private function generateNoteNumber(): string
    {
        $date = now()->format('ymd');
        $prefix = "DN-{$date}-";

        $lastNote = DebitNote::withTrashed()
            ->where('note_number', 'like', "{$prefix}%")
            ->orderBy('note_number', 'desc')
            ->first();

        if ($lastNote) {
            $lastSequence = (int) substr($lastNote->note_number, -3);
            $newSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '001';
        }

        return $prefix.$newSequence;
    }
}
