<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TripSettlement extends Model
{
    /** @use HasFactory, SoftDeletes<\Database\Factories\TripSettlementFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'settlement_code',
        'dispatch_order_id',
        'total_expense',
        'total_cash_advance',
        'balance',
        'status',
        'rejection_reason',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'total_expense' => 'decimal:2',
            'total_cash_advance' => 'decimal:2',
            'balance' => 'decimal:2',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
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
}
