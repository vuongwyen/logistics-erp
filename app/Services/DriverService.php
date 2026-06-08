<?php

namespace App\Services;

use App\Models\Driver;
use App\Support\VietnameseDate;

class DriverService
{
    public function getAll(array $filters = [], int $perPage = 10)
    {
        $query = Driver::with('user');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('license_number', 'like', "%{$search}%")
                    ->orWhere('driver_code', 'like', "%{$search}%")
                    ->orWhere('rank', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['rank'])) {
            $query->where('rank', $filters['rank']);
        }

        foreach (['driver_code', 'full_name', 'phone', 'license_number'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, 'like', "%{$filters[$field]}%");
            }
        }

        if (! empty($filters['date_of_birth'])) {
            $query->whereDate('date_of_birth', VietnameseDate::toDatabase($filters['date_of_birth']));
        }

        if (! empty($filters['contract_expiry'])) {
            $query->whereDate('contract_expiry', VietnameseDate::toDatabase($filters['contract_expiry']));
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data)
    {
        $data['driver_code'] = $this->generateDriverCode();

        return Driver::create($data);
    }

    public function update(Driver $driver, array $data)
    {
        $driver->update($data);

        return $driver;
    }

    public function delete(Driver $driver)
    {
        return $driver->delete();
    }

    private function generateDriverCode(): string
    {
        $date = now()->format('ym');
        $prefix = "TX-{$date}-";

        $lastDriver = Driver::withTrashed()
            ->where('driver_code', 'like', "{$prefix}%")
            ->orderBy('driver_code', 'desc')
            ->first();

        if ($lastDriver) {
            $lastSequence = (int) substr($lastDriver->driver_code, -3);
            $newSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '001';
        }

        return $prefix.$newSequence;
    }
}
