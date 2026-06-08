<?php

namespace App\Http\Requests;

use App\Support\VietnameseDate;
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

    protected function prepareForValidation(): void
    {
        $this->merge(VietnameseDate::normalizedFields($this->all(), ['expected_date']));

        if ($this->filled('container_number')) {
            $this->merge(['container_number' => strtoupper((string) $this->input('container_number'))]);
        }
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
            'container_number' => ['nullable', 'regex:/^[A-Z]{4}\d{7}$/'],
            'customs_declaration_no' => ['nullable', 'regex:/^\d{12}$/'],
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

                try {
                    $shippingJob = $this->route('shipping_job');
                    $createdDate = $shippingJob?->created_at?->copy()->startOfDay() ?? now()->startOfDay();
                    $expectedDate = Carbon::parse($this->input('expected_date'))->startOfDay();
                } catch (\Throwable) {
                    return;
                }

                if ($expectedDate->greaterThan($createdDate->copy()->addDays(10))) {
                    $validator->errors()->add('expected_date', 'Ngày dự kiến không được vượt quá 10 ngày so với ngày tạo đơn hàng.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'Vui lòng chọn khách hàng.',
            'pickup_location_id.required' => 'Vui lòng chọn địa điểm bốc hàng.',
            'delivery_location_id.required' => 'Vui lòng chọn địa điểm dỡ hàng.',
            'cargo_type.required' => 'Vui lòng nhập loại hàng hóa.',
            'container_number.regex' => 'Số container phải gồm 4 chữ cái đầu và 7 chữ số phía sau, ví dụ TCNU1234567.',
            'customs_declaration_no.regex' => 'Số tờ khai hải quan phải gồm đúng 12 chữ số.',
            'expected_date.required' => 'Vui lòng nhập ngày dự kiến.',
            'expected_date.date' => 'Ngày dự kiến phải đúng định dạng ngày/tháng/năm.',
        ];
    }
}
