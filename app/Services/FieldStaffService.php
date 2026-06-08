<?php

namespace App\Services;

use App\Models\FieldStaff;
use App\Support\VietnameseDate;

class FieldStaffService
{
    public function getAll(array $filters = [], int $perPage = 10)
    {
        $query = FieldStaff::with(['user', 'responsibleLocation', 'responsibleLocations']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('staff_code', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('certificates', 'like', "%{$search}%")
                    ->orWhereHas('responsibleLocations', function ($sub) use ($search) {
                        $sub->where('location_name', 'like', "%{$search}%")
                            ->orWhere('province', 'like', "%{$search}%");
                    })
                    ->orWhereHas('responsibleLocation', function ($sub) use ($search) {
                        $sub->where('location_name', 'like', "%{$search}%")
                            ->orWhere('province', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['responsible_location_id'])) {
            $locationId = $filters['responsible_location_id'];
            $query->where(function ($subQuery) use ($locationId) {
                $subQuery->where('responsible_location_id', $locationId)
                    ->orWhereHas('responsibleLocations', fn ($locationQuery) => $locationQuery->where('locations.id', $locationId));
            });
        }

        foreach (['staff_code', 'full_name', 'phone', 'certificates'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, 'like', "%{$filters[$field]}%");
            }
        }

        if (! empty($filters['date_of_birth'])) {
            $query->whereDate('date_of_birth', VietnameseDate::toDatabase($filters['date_of_birth']));
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): FieldStaff
    {
        $locationIds = $this->extractLocationIds($data);
        $data['staff_code'] = $this->generateStaffCode();
        $data['responsible_location_id'] = $locationIds[0] ?? $data['responsible_location_id'] ?? null;
        unset($data['responsible_location_ids']);

        $fieldStaff = FieldStaff::create($data);
        $fieldStaff->responsibleLocations()->sync($locationIds);

        return $fieldStaff;
    }

    public function update(FieldStaff $fieldStaff, array $data): FieldStaff
    {
        $locationIds = $this->extractLocationIds($data);
        $data['responsible_location_id'] = $locationIds[0] ?? $data['responsible_location_id'] ?? null;
        unset($data['responsible_location_ids']);

        $fieldStaff->update($data);
        $fieldStaff->responsibleLocations()->sync($locationIds);

        return $fieldStaff;
    }

    public function delete(FieldStaff $fieldStaff): bool
    {
        return $fieldStaff->delete();
    }

    private function generateStaffCode(): string
    {
        $date = now()->format('ym');
        $prefix = "HT-{$date}-";

        $lastStaff = FieldStaff::withTrashed()
            ->where('staff_code', 'like', "{$prefix}%")
            ->orderBy('staff_code', 'desc')
            ->first();

        if ($lastStaff) {
            $lastSequence = (int) substr($lastStaff->staff_code, -3);
            $newSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '001';
        }

        return $prefix.$newSequence;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<int>
     */
    private function extractLocationIds(array $data): array
    {
        $ids = $data['responsible_location_ids'] ?? [$data['responsible_location_id'] ?? null];

        return collect((array) $ids)
            ->filter()
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
