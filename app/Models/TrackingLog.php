<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrackingLog extends Model
{
    /** @use HasFactory, SoftDeletes<\Database\Factories\TrackingLogFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dispatch_order_id',
        'status_update',
        'updated_by',
        'latitude',
        'longitude',
    ];

    public function dispatchOrder(): BelongsTo
    {
        return $this->belongsTo(DispatchOrder::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }
}
