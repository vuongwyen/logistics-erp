<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\User;
use App\Notifications\LogisticsNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class ExpenseController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'dispatch_order_id' => 'required|exists:dispatch_orders,id',
            'expense_type' => 'required|string|max:100',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        $dispatchOrder = \App\Models\DispatchOrder::findOrFail($validated['dispatch_order_id']);

        $validated['shipping_job_id'] = $dispatchOrder->shipping_job_id;
        $validated['reported_by'] = Auth::id();
        $validated['status'] = 'approved'; // Default to approved for now

        $expense = Expense::create($validated);

        // Notify Admins and Accountants
        $notifiableUsers = User::whereHas('role', function ($q) {
            $q->whereIn('role_code', ['ADMIN', 'ACCOUNTANT']);
        })->get();

        $reporterName = Auth::user()->name;
        Notification::send($notifiableUsers, new LogisticsNotification(
            'Chi phí mới phát sinh',
            "{$reporterName} vừa báo cáo chi phí {$validated['expense_type']}: ".number_format($validated['amount']).' VNĐ',
            'fa-file-invoice-dollar',
            route('dispatch-orders.show', $validated['dispatch_order_id'])
        ));

        return back()->with('success', 'Ghi nhận chi phí thành công!');
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return back()->with('success', 'Đã xóa khoản chi phí.');
    }
}
