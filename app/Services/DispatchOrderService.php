<?php

namespace App\Services;

use App\Models\DispatchOrder;
use App\Models\ShippingJob;
use App\Support\VietnameseDate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DispatchOrderService
{
    public function getAll(array $filters = [], int $perPage = 10)
    {
        $query = DispatchOrder::with(['shippingJob.customer', 'vehicle', 'driver', 'creator', 'startLocation', 'endLocation'])
            ->latest();

        if (Auth::user()->hasRole('DRIVER')) {
            $driver = Auth::user()->driver;
            $query->where('driver_id', $driver ? $driver->id : 0);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('shippingJob', function ($sub) use ($search) {
                        $sub->where('job_code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('driver', function ($sub) use ($search) {
                        $sub->where('full_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('vehicle', function ($sub) use ($search) {
                        $sub->where('plate_number', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['dispatch_status'])) {
            $query->where('dispatch_status', $filters['dispatch_status']);
        }

        if (! empty($filters['approval_status'])) {
            $query->where('approval_status', $filters['approval_status']);
        }

        foreach (['order_number'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, 'like', "%{$filters[$field]}%");
            }
        }

        if (! empty($filters['driver_name'])) {
            $query->whereHas('driver', fn ($sub) => $sub->where('full_name', 'like', "%{$filters['driver_name']}%"));
        }

        if (! empty($filters['plate_number'])) {
            $query->whereHas('vehicle', fn ($sub) => $sub->where('plate_number', 'like', "%{$filters['plate_number']}%"));
        }

        if (! empty($filters['job_code'])) {
            $query->whereHas('shippingJob', fn ($sub) => $sub->where('job_code', 'like', "%{$filters['job_code']}%"));
        }

        if (! empty($filters['planned_departure_date'])) {
            $query->whereDate('planned_departure_date', VietnameseDate::toDatabase($filters['planned_departure_date']));
        }

        if (! empty($filters['planned_return_date'])) {
            $query->whereDate('planned_return_date', VietnameseDate::toDatabase($filters['planned_return_date']));
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): DispatchOrder
    {
        return DB::transaction(function () use ($data) {
            $data['order_number'] = $this->generateOrderNumber();
            $data['created_by'] = Auth::id();
            $data['dispatch_status'] = 'dispatched';
            $data['approval_status'] = 'pending';
            $data['loading_percent'] = (int) ($data['loading_percent'] ?? 0);

            $shippingJob = ShippingJob::find($data['shipping_job_id']);
            $data['start_location_id'] = $data['start_location_id'] ?? $shippingJob?->pickup_location_id;
            $data['end_location_id'] = $data['end_location_id'] ?? $shippingJob?->delivery_location_id;

            $dispatchOrder = DispatchOrder::create($data);

            // Create initial tracking log
            $dispatchOrder->trackingLogs()->create([
                'status_update' => 'pending_approval',
                'updated_by' => Auth::id(),
                'latitude' => $data['current_latitude'] ?? null,
                'longitude' => $data['current_longitude'] ?? null,
            ]);

            return $dispatchOrder;
        });
    }

    private function generateOrderNumber(): string
    {
        $date = now()->format('ymd');
        $prefix = "DO-{$date}-";

        $lastOrder = DispatchOrder::withTrashed()
            ->where('order_number', 'like', "{$prefix}%")
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastSequence = (int) substr($lastOrder->order_number, -3);
            $newSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '001';
        }

        return $prefix.$newSequence;
    }
}
