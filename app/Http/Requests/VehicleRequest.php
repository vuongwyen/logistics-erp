<?php

namespace App\Http\Requests;

use App\Support\LogisticsOptions;
use App\Support\VietnameseDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(VietnameseDate::normalizedFields($this->all(), ['registration_expiry']));

        if ($this->filled('plate_number')) {
            $this->merge(['plate_number' => strtoupper((string) $this->input('plate_number'))]);
        }
    }

    public function rules(): array
    {
        $vehicleId = $this->route('vehicle') ? $this->route('vehicle')->id : null;

        return [
            'plate_number' => ['required', 'regex:/^\d{2}[A-Z]{1,2}\d?-\d{3}\.\d{2}$/', 'unique:vehicles,plate_number,'.$vehicleId],
            'vehicle_type' => ['required', Rule::in(array_keys(LogisticsOptions::vehicleTypes()))],
            'payload' => ['required', 'numeric', Rule::in(array_keys(LogisticsOptions::payloads()))],
            'registration_expiry' => ['nullable', 'date'],
            'status' => ['required', 'in:available,busy,maintenance'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'plate_number.required' => 'Vui lòng nhập biển số xe.',
            'plate_number.regex' => 'Biển số xe phải đúng định dạng, ví dụ 15B2-923.15.',
            'plate_number.unique' => 'Biển số xe này đã tồn tại.',
            'vehicle_type.required' => 'Vui lòng nhập loại xe (vd: 5 tấn, 10 tấn, Container...).',
            'payload.required' => 'Vui lòng nhập tải trọng.',
            'payload.numeric' => 'Tải trọng phải là một con số.',
        ];
    }
}
