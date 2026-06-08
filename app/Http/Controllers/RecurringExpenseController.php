<?php

namespace App\Http\Controllers;

use App\Models\RecurringExpense;
use App\Support\VietnameseDate;
use Illuminate\Http\Request;

class RecurringExpenseController extends Controller
{
    public function store(Request $request)
    {
        $validated = $this->validatedData($request);

        $validated['expense_code'] = $this->generateExpenseCode();

        RecurringExpense::create($validated);

        return back()->with('success', 'Đã thêm chi phí cố định.');
    }

    public function update(Request $request, RecurringExpense $recurringExpense)
    {
        $recurringExpense->update($this->validatedData($request));

        return back()->with('success', 'Đã cập nhật chi phí cố định.');
    }

    public function destroy(RecurringExpense $recurringExpense)
    {
        $recurringExpense->delete();

        return back()->with('success', 'Đã xóa chi phí cố định.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $request->merge(VietnameseDate::normalizedFields($request->all(), ['effective_from', 'effective_to']));

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'cycle' => ['required', 'in:monthly,quarterly,yearly'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'status' => ['required', 'in:active,inactive'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
    }

    private function generateExpenseCode(): string
    {
        $prefix = 'FIX-';

        $lastExpense = RecurringExpense::withTrashed()
            ->where('expense_code', 'like', "{$prefix}%")
            ->orderBy('expense_code', 'desc')
            ->first();

        if ($lastExpense) {
            $lastSequence = (int) substr($lastExpense->expense_code, -4);
            $newSequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '0001';
        }

        return $prefix.$newSequence;
    }
}
