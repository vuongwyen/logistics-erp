<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;

class CustomerService
{
    /**
     * Get list of customers with pagination and search
     */
    public function getAll(array $filters = [], int $perPage = 10)
    {
        $query = Customer::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('tax_code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        foreach (['customer_code', 'customer_name', 'tax_code', 'email', 'contact_person', 'phone'] as $field) {
            if (! empty($filters[$field])) {
                $query->where($field, 'like', "%{$filters[$field]}%");
            }
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new customer
     */
    public function create(array $data)
    {
        $data = $this->normalizeCustomerData($data);
        if (empty($data['company_name'])) {
            $data['company_name'] = $data['customer_name'];
        }

        $attempts = 0;
        while (true) {
            try {
                if ($attempts === 0) {
                    $data['customer_code'] = $this->generateCustomerCode();
                } else {
                    $parts = explode('-', $data['customer_code']);
                    $sequence = (int) array_pop($parts);
                    $parts[] = str_pad($sequence + 1, 3, '0', STR_PAD_LEFT);
                    $data['customer_code'] = implode('-', $parts);
                }

                return Customer::create($data);
            } catch (UniqueConstraintViolationException $e) {
                if (! str_contains($e->getMessage(), 'customers_customer_code_unique')) {
                    throw $e;
                }
                $attempts++;
                if ($attempts >= 20) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Update an existing customer
     */
    public function update(Customer $customer, array $data)
    {
        unset($data['customer_code']);
        $data = $this->normalizeCustomerData($data);

        if (empty($data['company_name']) && ! empty($data['customer_name'])) {
            $data['company_name'] = $data['customer_name'];
        }
        $customer->update($data);

        return $customer;
    }

    /**
     * Delete a customer (Soft Delete)
     */
    public function delete(Customer $customer)
    {
        return $customer->delete();
    }

    private function generateCustomerCode(): string
    {
        $date = now()->format('ym');
        $prefix = "KH-{$date}-";

        $lastCustomer = Customer::withTrashed()
            ->where('customer_code', 'like', "{$prefix}%")
            ->orderBy('customer_code', 'desc')
            ->first();

        if ($lastCustomer) {
            $lastSequence = (int) substr($lastCustomer->customer_code, -3);
            $newSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newSequence = '001';
        }

        return $prefix.$newSequence;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeCustomerData(array $data): array
    {
        foreach (['customer_name', 'company_name', 'contact_person'] as $field) {
            if (! empty($data[$field]) && is_string($data[$field])) {
                $data[$field] = Str::of($data[$field])
                    ->squish()
                    ->lower()
                    ->title()
                    ->value();
            }
        }

        return $data;
    }
}
