<?php

namespace App\Services;

use App\Models\ShippingJob;
use App\Support\VietnameseDate;
use Illuminate\Support\Facades\Auth;

class ShippingJobService
{
    public function getAll(array $filters = [], int $perPage = 10)
    {
        $query = ShippingJob::with(['customer', 'pickupLocation', 'deliveryLocation', 'creator'])
            ->withCount(['dispatchOrders', 'documents', 'expenses'])
            ->latest();

        if (Auth::user()->hasRole('DRIVER')) {
            $driver = Auth::user()->driver;
            $driverId = $driver ? $driver->id : 0;
            $query->whereHas('dispatchOrders', function ($q) use ($driverId) {
                $q->where('driver_id', $driverId);
            });
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('job_code', 'like', "%{$search}%")
                    ->orWhere('container_number', 'like', "%{$search}%")
                    ->orWhere('customs_declaration_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($sub) use ($search) {
                        $sub->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        foreach (['job_code', 'container_number', 'customs_declaration_no', 'cargo_type', 'container_type'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, 'like', "%{$filters[$field]}%");
            }
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('expected_date', '>=', VietnameseDate::toDatabase($filters['date_from']));
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('expected_date', '<=', VietnameseDate::toDatabase($filters['date_to']));
        }

        if (! empty($filters['created_date'])) {
            $query->whereDate('created_at', VietnameseDate::toDatabase($filters['created_date']));
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): ShippingJob
    {
        $data['job_code'] = $this->generateJobCode();
        $data['created_by'] = Auth::id();
        $data['status'] = 'new';

        return ShippingJob::create($data);
    }

    public function update(ShippingJob $shippingJob, array $data): bool
    {
        return $shippingJob->update($data);
    }

    public function delete(ShippingJob $shippingJob): bool
    {
        return $shippingJob->delete();
    }

    private function generateJobCode(): string
    {
        $date = now()->format('Ym');
        $prefix = "JOB-{$date}-";

        $lastJob = ShippingJob::where('job_code', 'like', "{$prefix}%")
            ->orderBy('job_code', 'desc')
            ->first();

        if ($lastJob) {
            $lastSequence = (int) substr($lastJob->job_code, -3);
            $newSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '001';
        }

        return $prefix.$newSequence;
    }
}
