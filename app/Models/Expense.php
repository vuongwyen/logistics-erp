<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    /** @use HasFactory, SoftDeletes<\Database\Factories\ExpenseFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shipping_job_id',
        'dispatch_order_id',
        'expense_type',
        'amount',
        'note',
        'document_id',
        'reported_by',
        'status',
    ];

    public function shippingJob(): BelongsTo
    {
        return $this->belongsTo(ShippingJob::class);
    }

    public function dispatchOrder(): BelongsTo
    {
        return $this->belongsTo(DispatchOrder::class);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    protected static function booted(): void
    {
        static::updating(function (Expense $expense) {
            if ($expense->dispatch_order_id && $expense->dispatchOrder->tripSettlement) {
                throw new \Exception('Không thể sửa chi phí của lệnh điều vận đã có quyết toán.');
            }
        });

        static::deleting(function (Expense $expense) {
            if ($expense->dispatch_order_id && $expense->dispatchOrder->tripSettlement) {
                throw new \Exception('Không thể xóa chi phí của lệnh điều vận đã có quyết toán.');
            }
        });
    }
}
