<?php

namespace App\Http\Requests;

use App\Support\LogisticsOptions;
use App\Support\VietnameseDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(VietnameseDate::normalizedFields($this->all(), [
            'date_of_birth',
            'start_date',
            'contract_expiry',
        ]));

        if (is_string($this->input('phone'))) {
            $this->merge(['phone' => trim($this->input('phone'))]);
        }
    }

    public function rules(): array
    {
        $driverId = $this->route('driver') ? $this->route('driver')->id : null;

        return [
            'user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereIn('role_id', function ($subQuery) {
                        $subQuery->select('id')
                            ->from('roles')
                            ->where('role_code', 'DRIVER');
                    });
                }),
                Rule::unique('drivers', 'user_id')->ignore($driverId),
            ],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^0\d{9}$/'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'license_number' => ['required', 'string', 'max:50', 'unique:drivers,license_number,'.$driverId],
            'status' => ['required', 'in:active,inactive'],
            'start_date' => ['nullable', 'date'],
            'rank' => ['nullable', Rule::in(array_keys(LogisticsOptions::driverRanks()))],
            'contract_expiry' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' => 'Tài khoản liên kết phải thuộc vai trò Tài xế.',
            'user_id.unique' => 'Tài khoản này đã được liên kết với một tài xế khác.',
            'full_name.required' => 'Vui lòng nhập họ tên tài xế.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại phải gồm đúng 10 số và bắt đầu bằng 0.',
            'date_of_birth.required' => 'Vui lòng nhập ngày sinh tài xế.',
            'date_of_birth.date' => 'Ngày sinh phải đúng định dạng ngày/tháng/năm.',
            'license_number.required' => 'Vui lòng nhập số bằng lái.',
            'license_number.unique' => 'Số bằng lái này đã tồn tại.',
        ];
    }
}
