<?php

namespace App\Http\Requests;

use App\Support\VietnameseDate;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DispatchOrderRequest extends FormRequest
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
        $this->merge(VietnameseDate::normalizedFields($this->all(), [
            'planned_departure_date',
            'planned_return_date',
        ]));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'shipping_job_id' => ['required', 'exists:shipping_jobs,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'trailer_id' => ['nullable', 'exists:vehicles,id', 'different:vehicle_id'],
            'driver_id' => ['required', 'exists:drivers,id'],
            'start_location_id' => ['nullable', 'exists:locations,id'],
            'end_location_id' => ['nullable', 'exists:locations,id'],
            'planned_departure_date' => ['required', 'date'],
            'planned_return_date' => ['required', 'date', 'after_or_equal:planned_departure_date'],
            'note' => ['nullable', 'string', 'max:500'],
            'fuel_quota' => ['nullable', 'numeric', 'min:0'],
            'fuel_price_quota' => ['nullable', 'numeric', 'min:0'],
            'toll_quota' => ['nullable', 'numeric', 'min:0'],
            'loading_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'current_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'current_longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_job_id.required' => 'Vui lòng chọn đơn hàng.',
            'vehicle_id.required' => 'Vui lòng chọn xe.',
            'driver_id.required' => 'Vui lòng chọn tài xế.',
            'planned_departure_date.required' => 'Vui lòng nhập ngày đi.',
            'planned_departure_date.date' => 'Ngày đi phải đúng định dạng ngày/tháng/năm.',
            'planned_return_date.required' => 'Vui lòng nhập ngày về.',
            'planned_return_date.date' => 'Ngày về phải đúng định dạng ngày/tháng/năm.',
            'planned_return_date.after_or_equal' => 'Ngày về không được trước ngày đi.',
        ];
    }
}
