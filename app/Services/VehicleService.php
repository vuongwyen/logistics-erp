<?php

namespace App\Services;

use App\Models\Vehicle;
use App\Support\VietnameseDate;

class VehicleService
{
    public function getAll(array $filters = [], int $perPage = 10)
    {
        $query = Vehicle::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                    ->orWhere('vehicle_type', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        foreach (['plate_number', 'vehicle_type', 'note'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, 'like', "%{$filters[$field]}%");
            }
        }

        if (! empty($filters['payload'])) {
            $query->where('payload', $filters['payload']);
        }

        if (! empty($filters['registration_expiry'])) {
            $query->whereDate('registration_expiry', VietnameseDate::toDatabase($filters['registration_expiry']));
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data)
    {
        return Vehicle::create($data);
    }

    public function update(Vehicle $vehicle, array $data)
    {
        $vehicle->update($data);

        return $vehicle;
    }

    public function delete(Vehicle $vehicle)
    {
        return $vehicle->delete();
    }
}
