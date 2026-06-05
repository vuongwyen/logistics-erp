<?php

namespace App\Http\Controllers;

use App\Models\TripSettlement;
use App\Models\DispatchOrder;
use App\Models\Expense;
use App\Models\CashAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class TripSettlementController extends Controller
{
    public function index(Request $request)
    {
        $query = TripSettlement::with(['dispatchOrder.driver', 'dispatchOrder.vehicle', 'requester', 'approver']);

        if ($request->filled('settlement_code')) {
            $query->where('settlement_code', 'like', '%' . $request->settlement_code . '%');
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $settlements = $query->latest()->paginate(15)->withQueryString();
        
        $dispatchOrders = DispatchOrder::whereDoesntHave('tripSettlement')->with(['driver', 'vehicle'])->get();

        return view('trip_settlements.index', compact('settlements', 'dispatchOrders'));
    }

    public function getOrderFinancials($id)
    {
        $dispatchOrder = DispatchOrder::findOrFail($id);
        
        if ($dispatchOrder->tripSettlement) {
            return response()->json(['error' => 'Lệnh này đã được quyết toán.'], 400);
        }

        $totalExpense = Expense::where('dispatch_order_id', $id)->sum('amount');
        $totalAdvance = CashAdvance::where('dispatch_order_id', $id)->where('status', 'approved')->sum('amount');
        $balance = $totalAdvance - $totalExpense;

        return response()->json([
            'total_expense' => $totalExpense,
            'total_cash_advance' => $totalAdvance,
            'balance' => $balance
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'dispatch_order_id' => 'required|exists:dispatch_orders,id',
            'note' => 'nullable|string'
        ]);

        $dispatchOrder = DispatchOrder::findOrFail($request->dispatch_order_id);

        if ($dispatchOrder->tripSettlement) {
            return redirect()->back()->with('error', 'Lệnh điều vận này đã được quyết toán.');
        }

        $totalExpense = Expense::where('dispatch_order_id', $dispatchOrder->id)->sum('amount');
        $totalAdvance = CashAdvance::where('dispatch_order_id', $dispatchOrder->id)->where('status', 'approved')->sum('amount');
        $balance = $totalAdvance - $totalExpense;

        TripSettlement::create([
            'settlement_code' => 'QT-' . strtoupper(Str::random(6)),
            'dispatch_order_id' => $dispatchOrder->id,
            'total_expense' => $totalExpense,
            'total_cash_advance' => $totalAdvance,
            'balance' => $balance,
            'status' => 'submitted',
            'requested_by' => Auth::id(),
            'requested_at' => now(),
            'note' => $request->note,
        ]);

        return redirect()->route('trip-settlements.index')->with('success', 'Đã tạo quyết toán thành công.');
    }

    public function update(Request $request, TripSettlement $tripSettlement)
    {
        $request->validate([
            'note' => 'nullable|string'
        ]);

        if ($tripSettlement->status !== 'draft' && $tripSettlement->status !== 'submitted') {
            return redirect()->back()->with('error', 'Không thể sửa quyết toán đã duyệt hoặc bị từ chối.');
        }

        $tripSettlement->update([
            'note' => $request->note
        ]);

        return redirect()->route('trip-settlements.index')->with('success', 'Cập nhật quyết toán thành công.');
    }

    public function approve(Request $request, TripSettlement $tripSettlement)
    {
        if ($tripSettlement->status !== 'submitted') {
            return redirect()->back()->with('error', 'Chỉ có thể duyệt quyết toán đang chờ duyệt.');
        }

        $tripSettlement->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('trip-settlements.index')->with('success', 'Đã duyệt quyết toán.');
    }

    public function reject(Request $request, TripSettlement $tripSettlement)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        if ($tripSettlement->status !== 'submitted') {
            return redirect()->back()->with('error', 'Chỉ có thể từ chối quyết toán đang chờ duyệt.');
        }

        $tripSettlement->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return redirect()->route('trip-settlements.index')->with('success', 'Đã từ chối quyết toán.');
    }

    public function destroy(TripSettlement $tripSettlement)
    {
        if ($tripSettlement->status === 'approved') {
            return redirect()->back()->with('error', 'Không thể xóa quyết toán đã duyệt.');
        }

        $tripSettlement->delete();

        return redirect()->route('trip-settlements.index')->with('success', 'Đã xóa quyết toán.');
    }
}
