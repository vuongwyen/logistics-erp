<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ShippingJobRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'pickup_location_id' => ['required', 'exists:locations,id'],
            'delivery_location_id' => ['required', 'exists:locations,id'],
            'cargo_type' => ['required', 'string', 'max:100'],
            'container_type' => ['nullable', 'string', 'max:50'],
            'service_price_id' => ['nullable', 'exists:service_prices,id'],
            'container_number' => ['nullable', 'string', 'max:50'],
            'customs_declaration_no' => ['nullable', 'string', 'max:50'],
            'expected_date' => ['required', 'date'],
            'status' => ['nullable', 'in:new,processing,dispatched,completed,cancelled'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (! $this->filled('expected_date')) {
                    return;
                }

                $shippingJob = $this->route('shipping_job');
                $createdDate = $shippingJob?->created_at?->copy()->startOfDay() ?? now()->startOfDay();
                $expectedDate = Carbon::parse($this->input('expected_date'))->startOfDay();

                if ($expectedDate->greaterThan($createdDate->copy()->addDays(10))) {
                    $validator->errors()->add('expected_date', 'Ngày dự kiến không được vượt quá 10 ngày so với ngày tạo đơn hàng.');
                }
            },
        ];
    }
}
