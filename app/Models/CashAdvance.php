<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashAdvance extends Model
{
    /** @use HasFactory, SoftDeletes<\Database\Factories\CashAdvanceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shipping_job_id',
        'dispatch_order_id',
        'requested_by',
        'approved_by',
        'amount',
        'reason',
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

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected static function booted(): void
    {
        static::updating(function (CashAdvance $advance) {
            if ($advance->dispatch_order_id && $advance->dispatchOrder->tripSettlement) {
                throw new \Exception('Không thể sửa tạm ứng của lệnh điều vận đã có quyết toán.');
            }
        });

        static::deleting(function (CashAdvance $advance) {
            if ($advance->dispatch_order_id && $advance->dispatchOrder->tripSettlement) {
                throw new \Exception('Không thể xóa tạm ứng của lệnh điều vận đã có quyết toán.');
            }
        });
    }
}
